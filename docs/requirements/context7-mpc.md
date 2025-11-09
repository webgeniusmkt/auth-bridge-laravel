# Context7 MCP – Integration and Usage Guide

_Last updated: 2025-10-24_

## Purpose

This document explains how **Context7 MCP** is integrated and used across the project.  
It ensures that all AI assistants (Windsurf, Claude, Gemini, etc.) use the correct Context7 MCP tools and environment when generating, reviewing, or debugging code.

---

## Overview

**Context7 MCP** provides **real-time, version-specific documentation** for any library or framework.  
When AI assistants use Context7, they access the most current, accurate API references and examples directly from library maintainers.

### Without Context7
❌ Outdated code examples  
❌ Hallucinated APIs  
❌ Inaccurate or deprecated method names

### With Context7
✅ Up-to-date version-specific documentation  
✅ Real-world code samples pulled directly from the source  
✅ No hallucinated or outdated APIs

---

## When to Use Context7 MCP

Always invoke Context7 MCP when:

- Writing or updating **code**, **tests**, or **configuration**.
- Installing or configuring **frameworks/libraries**.
- Performing **API integration**, **SDK usage**, or **dependency setup**.
- Reviewing **breaking changes**, **version updates**, or **migrating** packages.

> **Example prompt:**
> “Implement authentication middleware using Laravel Passport.  
> **use context7**”

---

## Assistant Rules for Windsurf / Claude / Gemini

All assistants must:

1. Use Context7 MCP automatically for any **code, setup, or API documentation** prompt.
2. Prefer latest Context7 version.
3. When fetching docs, specify:
   - The **library name** (or Context7-compatible ID)
   - Optional **topic** (e.g. “authentication”, “queueing”, “routing”)
   - Optional **token limit** (default: 5000)
