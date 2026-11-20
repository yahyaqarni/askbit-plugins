---
name: bit-leads
description: Read and summarise a Bit site's leads and visitor conversations through the Bit MCP tools. Use when the person asks about new leads, who to follow up with, what visitors are asking, why a conversation went wrong, or what the assistant should learn next.
---

# Leads and conversations

Bit's assistant collects leads (people who left their details, sent a brief, or tapped call, WhatsApp or email) and keeps the conversations. Start with `list_sites` and pass the site's `id` as `site`.

## Leads

`list_leads` returns leads newest first. Narrow it with `status` (`open` means new or contacted), `kind` and `days`, and page with `next` as `cursor`.

When summarising:
- Lead with what needs doing: open leads, oldest first, each with the name, what they want (the `summary`), how to reach them, and the page they started on.
- Group the rest by status (won, lost, contacted) in a line each.
- Leads hold personal details. Use them only for the business's own follow-up. Don't paste them into other tools, files or services unless the person asks for that specific place.

Changing a lead's status is done in the dashboard (Leads). Say so if asked.

## Conversations

- `list_conversations` lists them newest first, with `intent` (`hot`, `warm`, `browsing`), the outcome and, for roles that may read transcripts, the visitor's first message and a summary.
- `get_conversation` with an `id` (from either list) returns the messages and any leads. Reading a transcript is written to the access log, so read the ones you need, not every one.

## Turning conversations into fixes

When the person asks what Bit should learn:
1. Look for repeated questions, wrong answers and conversations that ended without help.
2. Check `list_questions` too: those are the gaps the assistant noticed itself.
3. Suggest the fix in plain words (a fact, a document, a page to add), and make it with the `bit-setup` skill once the person agrees.

Report patterns with a count and one or two short examples, not whole transcripts.
