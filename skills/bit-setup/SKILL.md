---
name: bit-setup
description: Set up or improve a Bit site (the AI assistant on a business's website) through the Bit MCP tools. Use when the person asks to set up Bit, teach the assistant something, add their docs or policies, fix a wrong or missing answer, read the website again, or check what Bit knows.
---

# Setting up a Bit site

Bit is an AI assistant that answers visitors on a business's website, collects leads and hands over to the team. The `bit` MCP server (tools such as `list_sites`) changes one or more of the person's Bit sites. Every change goes in the business's access log under the key's name, so say what you're about to change.

## 1. Find the site

1. Call `list_sites`. If it lists several, ask which one, unless the person already named it. Pass its `id` as `site` to every other tool.
2. A site with `mcp: false` is on a plan without the MCP server. Say so and stop: they upgrade in the dashboard (Billing).
3. Call `get_site` for the plan, what it includes, the tools, the knowledge totals and any unpublished changes.

If a tool answers that the key lacks a permission, tell the person which one and that they can make a new key in Bit (Settings, Advanced, API keys, “Pick what an AI tool needs”). Don't retry.

## 2. What the assistant knows

Check first with `list_knowledge` (totals per source, then documents). Then pick the smallest change that fits:

| The person has | Use |
|---|---|
| A public page on their site, or anywhere public | `add_link` with its address |
| Text, a file in this repo, a policy, a price list | `add_document`: a short unique `title` and the text in Markdown. The same title replaces the document |
| One short fact ("We deliver to Lahore and Karachi only") | `add_fact`: a `topic` of a few words, the `answer`, and the visitor's `question` when it differs |
| A site that changed a lot | `start_crawl`, then `crawl_status` until it finishes (it reads the website in the background; tell the person it takes a few minutes and keep working) |
| A document that's wrong or out of date | `remove_knowledge` with its address from `list_knowledge`, then add the right one |

Knowledge changes reach visitors at once. There is no draft for knowledge.

When turning files from this repository into documents:
- One document per topic (pricing, delivery, returns, warranty), written for a visitor, not a developer. Leave out code, internal notes and anything about how the business runs inside.
- Never add secrets, passwords, API keys, card numbers, or personal details about customers or staff.
- Keep the person's own wording for prices, policies and promises. Don't invent or round them.

## 3. Questions Bit couldn't answer

`list_questions` shows what visitors asked that the assistant couldn't answer. For each one:
- If the person, the repo or the site's knowledge has the answer, call `answer_question` with its `id` and the `answer` in the business's voice.
- If it doesn't apply to the business, call `answer_question` with `skip: true`.
- If nobody knows, leave it and list it for the person at the end.

Ask the person before answering anything about prices, refunds, legal terms or medical, financial or safety matters.

## 4. Check the answer (optional)

`ask_bit` asks the assistant one question as a visitor would. Each question is a real model call that counts against the business's messages, and keys only have it when the person ticked “Ask the assistant test questions”. Use it only when the person wants to check an answer, one or two questions at a time, never in a loop. Pass `draft: true` to try unpublished settings.

## 5. Publishing settings changes

Knowledge is live at once, but changes to the site's own tools (see the `bit-custom-tool` skill) wait in the unpublished settings:

1. `pending_changes` lists what changed and returns `savedAt`.
2. Show the person the list in plain words and ask whether to publish.
3. Only after a clear yes, call `publish` with that `savedAt` and a short `note` for the history. If someone saved since, it refuses: call `pending_changes` again and show the new list.

The team can roll back any version in the dashboard (Settings, History).

## Finish

Tell the person what changed, what's live, anything left unpublished, and the questions nobody could answer. Link to the dashboard address from `get_site` for anything that has to be done there (secrets for an existing API, billing, the team).
