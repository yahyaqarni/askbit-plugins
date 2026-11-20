# Check an order: an endpoint for Bit's "checkOrder" tool.
# pip install fastapi uvicorn    then    TOOL_CHECK_ORDER_SECRET=... uvicorn check_order:app
import hashlib
import hmac
import json
import os
import time

from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse

SECRET = os.environ.get("TOOL_CHECK_ORDER_SECRET", "")
app = FastAPI()


@app.post("/bit/check-order")
async def check_order(request: Request):
    body = (await request.body()).decode()
    if not from_bit(body, request.headers.get("x-bit-signature", "")):
        return JSONResponse({"error": "Bad signature"}, status_code=401)
    data = json.loads(body)
    order_number = data["input"].get("orderNumber")  # The order number from the confirmation email

    # TODO: look it up in your own system (database, order system, courier API).
    result = {
        "found": True,
        "status": "Ready",
        "detail": "Whatever the visitor needs to hear, in plain field names",
    }

    # Nothing matched? Say so, and the assistant tells the visitor: return {"found": False}
    return result


def from_bit(body: str, header: str) -> bool:
    """True when the request really comes from Bit: a valid signature from the last 5 minutes."""
    parts = dict(p.split("=", 1) for p in header.split(",") if "=" in p)
    t, v1 = parts.get("t", ""), parts.get("v1", "")
    if not SECRET or not t.isdigit() or abs(time.time() - int(t)) > 300:
        return False
    want = hmac.new(SECRET.encode(), f"{t}.{body}".encode(), hashlib.sha256).hexdigest()
    return hmac.compare_digest(want, v1)
