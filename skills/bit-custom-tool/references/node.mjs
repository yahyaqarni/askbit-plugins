// Check an order: an endpoint for Bit's "checkOrder" tool.
// npm install express    then    TOOL_CHECK_ORDER_SECRET=... node check-order.mjs
import { createHmac, timingSafeEqual } from "node:crypto";
import express from "express";

const SECRET = process.env.TOOL_CHECK_ORDER_SECRET;
const app = express();

// Bit signs the raw body, so read it as text before parsing it.
app.post("/bit/check-order", express.text({ type: "application/json", limit: "64kb" }), async (req, res) => {
  if (!fromBit(req.body, req.get("x-bit-signature") ?? "")) return res.status(401).json({ error: "Bad signature" });
  const { input } = JSON.parse(req.body);
  const orderNumber = input.orderNumber; // The order number from the confirmation email

  // TODO: look it up in your own system (database, order system, courier API).
  const result = {
    "found": true,
    "status": "Ready",
    "detail": "Whatever the visitor needs to hear, in plain field names"
  };

  // Nothing matched? Say so, and the assistant tells the visitor: res.json({ found: false })
  res.json(result);
});

/** True when the request really comes from Bit: a valid signature from the last 5 minutes. */
function fromBit(body, header) {
  if (!SECRET || typeof body !== "string") return false;
  const parts = Object.fromEntries(header.split(",").map((p) => p.split("=")));
  if (!parts.t || Math.abs(Date.now() / 1000 - Number(parts.t)) > 300) return false;
  const want = createHmac("sha256", SECRET).update(`${parts.t}.${body}`).digest();
  const got = Buffer.from(parts.v1 ?? "", "hex");
  return got.length === want.length && timingSafeEqual(got, want);
}

app.listen(process.env.PORT ?? 3000);
