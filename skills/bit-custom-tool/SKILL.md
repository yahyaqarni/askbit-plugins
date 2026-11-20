---
name: bit-custom-tool
description: Connect a business's own system to Bit (the AI assistant on its website) as a look-up tool, such as order status, parcel tracking, bookings or stock, through the Bit MCP tools. Use when the person asks to give Bit access to an API or endpoint, to build an endpoint for Bit in this codebase, or to fix, test or remove one of the site's tools.
---

# A look-up tool for Bit

A Bit site can have its own tools: HTTPS endpoints the assistant calls with details the visitor gave (an order number, a tracking number) to answer with live data. Tools are read-only look-ups. They're never for taking payments or changing records.

Start with `list_sites`, pass the site's `id` as `site`, then `list_tools` to see the tools as saved, which are `live`, and how many the plan allows (`plan.limit`). A plan without own tools can't use this; say so.

## Choose how it connects

1. **An existing API** (most businesses already have one): set `request`. Bit calls it as it is, with the API's key, and reads only the fields in `pick`.
2. **An endpoint built for Bit**: leave out `request`. Bit sends a signed POST and the endpoint answers JSON. Best when you're writing the endpoint in this codebase, or when the existing API needs logic (combining calls, hiding fields).

An existing API whose key Bit doesn't have yet needs one step in the Bit dashboard: Settings, Tools, the tool's card, paste the key. Through MCP you can only use a secret made with `make_tool_secret`, or the one a live tool already sends to the same host. If the person has an API key for an existing API, tell them to paste it in the dashboard. Never ask them to paste it into this chat.

## The tool definition (`save_tool`'s `tool`)

| Field | Rule |
|---|---|
| `name` | camelCase, 2 to 40 letters and digits: `trackParcel`. Not a built-in tool's name |
| `label` | What the team sees, up to 40 characters: "Track a parcel" |
| `description` | For the assistant, 10 to 1000 characters: when to use it, what to ask the visitor for, what comes back |
| `inputSchema` | `{ "type": "object", "properties": {…}, "required": [...] }`, up to 8 camelCase fields, each `string`, `number`, `integer` or `boolean` with a `description`. Text fields may have an `enum` |
| `url` | Public HTTPS. For an existing API it may hold `{field}` placeholders in the path or query, never the host |
| `secretEnv` | The secret's name: the signing secret (built for Bit) or the API's key (existing API). Leave out only with `request.auth: "none"` |
| `request` | Existing API only: `method` (`GET` or `POST`), `auth` (`bearer`, `header` with `header` such as `X-API-Key`, `basic`, or `none`), and `pick`, the dotted paths the assistant reads (`status`, `shipment.lastScan`, `items.0.name`) |

Fields not in the address go in the query (GET) or a JSON body of the fields alone (POST). A 404 means "nothing found", and the assistant says so.

## Building an endpoint for Bit in this codebase

1. Agree the tool's `name` and fields with the person.
2. `make_tool_secret` with the tool's `name` returns a secret `name` and `value`, once. Tell the person to put the value in the endpoint's environment (their host's secret settings, or a git-ignored `.env`) under that name. Never write the value into a committed file, a log or the tool definition.
3. Write the endpoint in the project's own framework and style. Starting points that already check the signature: [Node (Express)](references/node.mjs), [PHP (Laravel)](references/laravel.php) and [Python (FastAPI)](references/fastapi.py). The rules, if you write it another way:
   - Bit sends `POST` with JSON `{ "tool", "site", "input", "sentAt" }` and headers `x-bit-tool: <name>` and `x-bit-signature: t=<unix seconds>,v1=<hex>`.
   - `v1` is HMAC-SHA256, keyed with the secret, of `${t}.${rawBody}`. Check it against the raw body before parsing, with a constant-time compare, and refuse a `t` more than 300 seconds from now with 401.
   - Answer 2xx with a JSON object or list of at most 16 KB, within 5 seconds, using plain field names the assistant can read out (`status`, `expectedDelivery`). When nothing matches, answer `{ "found": false }`.
   - Return only what a visitor may hear: no internal notes, other customers' data, costs or staff names.
4. Once it's deployed at a public HTTPS address, continue below with `url` set to it and `secretEnv` set to the secret's name.

## Save, test, publish

1. `test_tool` with the `tool` definition (or a saved tool's `name`) and an `input` the way the assistant would send it. It makes one real call and shows the status, time and answer. For an existing API, `fields` lists every path in the answer: choose `pick` from it, only what the visitor needs. Tests are limited (20 per 10 minutes per key), so fix what failed before the next one.
2. `save_tool` with the definition. It goes into the unpublished settings, and the same `name` replaces it.
3. `pending_changes`, show the person what changed, and `publish` with its `savedAt` only after they agree.

To take a tool away: `remove_tool`, then publish the same way.

## When a test fails

| Answer | Likely cause |
|---|---|
| 401 or 403 from an endpoint built for Bit | The endpoint's secret value isn't the one `make_tool_secret` returned, or it hashes the parsed body instead of the raw one |
| 401 or 403 from an existing API | The pasted key is wrong or needs another `auth` (header name, basic) |
| Timeout | The endpoint takes over 5 seconds, or isn't public (localhost and private networks are refused) |
| "Not JSON" or "too large" | Answer `application/json`, and trim it to what the assistant needs (16 KB) |
