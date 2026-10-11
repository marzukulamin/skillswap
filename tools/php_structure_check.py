from __future__ import annotations

import re
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent
FILES = [path for path in ROOT.rglob("*.php") if "tmp" not in path.parts]
PAIRS = {")": "(", "]": "[", "}": "{"}
ERRORS: list[str] = []
ROUTES: set[str] = set()


for path in FILES:
    text = path.read_text(encoding="utf-8")
    ROUTES.update(re.findall(r"url\(\s*['\"]([^'\"]+?\.php)", text))
    chunks = [
        match.group(1)
        for match in re.finditer(r"<\?(?:php|=)(.*?)(?:\?>|$)", text, re.I | re.S)
    ]
    code = "\n".join(chunks)
    stack: list[str] = []
    state = "code"
    quote = ""
    index = 0

    while index < len(code):
        char = code[index]
        following = code[index + 1] if index + 1 < len(code) else ""

        if state == "code":
            if char in ('"', "'"):
                state = "string"
                quote = char
            elif char == "/" and following == "/":
                state = "line_comment"
                index += 1
            elif char == "#":
                state = "line_comment"
            elif char == "/" and following == "*":
                state = "block_comment"
                index += 1
            elif char in "([{":
                stack.append(char)
            elif char in ")]}":
                if not stack or stack[-1] != PAIRS[char]:
                    ERRORS.append(f"{path}: unmatched {char}")
                    break
                stack.pop()
        elif state == "string":
            if char == "\\":
                index += 1
            elif char == quote:
                state = "code"
        elif state == "line_comment":
            if char == "\n":
                state = "code"
        elif state == "block_comment":
            if char == "*" and following == "/":
                state = "code"
                index += 1

        index += 1

    if stack:
        ERRORS.append(f"{path}: unclosed {''.join(stack)}")
    if state in {"string", "block_comment"}:
        ERRORS.append(f"{path}: unterminated {state}")

for route in sorted(ROUTES):
    route_path = ROOT / route.split("?", 1)[0]
    if not route_path.is_file():
        ERRORS.append(f"Missing local route target: {route}")


print(f"Checked balanced PHP source structure in {len(FILES)} files.")
print(f"Checked {len(ROUTES)} statically referenced local routes.")
if ERRORS:
    print("\n".join(ERRORS))
    sys.exit(1)
print("PASS: brackets, strings, and comments are balanced.")
