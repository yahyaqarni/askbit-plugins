# Bit for AI coding tools

[Bit](https://askbit.io) is an AI assistant that answers visitors on your website, collects leads and hands over to your team. This plugin lets Claude Code, Cursor, Codex and Antigravity set it up from your editor:

- **Knowledge:** add your docs, policies and facts, read your website again, answer the questions Bit couldn't.
- **Your own tools:** connect order status, parcel tracking or bookings, or build an endpoint for Bit in your codebase. It's tested, and goes live only when you say so.
- **Leads and conversations:** see who to follow up with, and what Bit should learn next.

It connects to Bit's MCP server (`https://askbit.io/mcp`) and adds three skills: `bit-setup`, `bit-custom-tool` and `bit-leads`.

## You need

A Bit API key: in Bit, open **Settings, Advanced, API keys**, make a key and press **Pick what an AI tool needs**. Choose **Every site in your organization** if you look after several sites. The MCP server is on the Growth and Scale plans (Business and up in Pakistan).

A key never does more than the person who made it can do, and everything it does shows in Bit under **Team, Access**.

## Install

**Claude Code**

```
/plugin marketplace add yahyaqarni/askbit-plugins
/plugin install askbit@askbit
```

It asks for your API key and keeps it in your system's secure storage.

**Codex**

```sh
codex plugin marketplace add yahyaqarni/askbit-plugins
```

Then install **Bit** from the Plugins directory. It asks for your API key.

**Antigravity**

```sh
agy plugin install https://github.com/yahyaqarni/askbit-plugins
```

Then add your API key under `/plugin` (CLI) or Customizations (app).

**Cursor**

Until Bit is in the Cursor marketplace, add the server to `.cursor/mcp.json`, with your key in an environment variable called `BIT_API_KEY` rather than in the file:

```json
{
  "mcpServers": {
    "bit": {
      "url": "https://askbit.io/mcp",
      "headers": { "Authorization": "Bearer ${env:BIT_API_KEY}" }
    }
  }
}
```

**Anything else that speaks MCP:** add `https://askbit.io/mcp` (Streamable HTTP) with the header `Authorization: Bearer <your key>`. Bit's Settings shows the exact lines for VS Code.

## Try

- "Show me my Bit sites and what each assistant knows."
- "Add our returns policy from `docs/returns.md` to Bit, then list the questions it couldn't answer."
- "Turn our order-status endpoint into a Bit tool, test it, and show me what will change before publishing."
- "Summarise this week's open Bit leads."

## More

- What each tool does: [askbit.io/developers](https://askbit.io/developers#mcp)
- Help: [askbit.io/help](https://askbit.io/help)
