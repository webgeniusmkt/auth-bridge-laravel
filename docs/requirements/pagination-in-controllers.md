# Pagination, Sorting & Filtering (Laravel APIs)

This guide describes how REST endpoints should expose pagination, server-side filtering, and sorting. All new list endpoints must follow these patterns unless explicitly exempted.

## When to Paginate

- **Paginate all unbounded lists** (e.g., issues, accounts, audit events, jobs, users).
- Skip pagination only when the total set is trivially small or static (e.g., enum lookups).
- Defer heavy child payloads (time series, nested logs) to a secondary endpoint or lazy-loading pattern.

## Pagination Styles

| Style | URL pattern | Use when | Notes |
|-------|-------------|----------|-------|
| Offset (default) | `?page=2&per_page=50` | Admin/reporting lists, sortable tables with “go to page” | Backed by `->paginate()`; include total counts. |
| Cursor | `?cursor=eyJpZCI6...&limit=50` | Large, append-only, or time-ordered feeds (events, logs) | Backed by `->cursorPaginate()`. Order by unique indexed column (e.g., `(created_at, id)`). |

Choose the style per endpoint and keep the response envelope consistent.

## Request Parameters

Offset endpoints must accept:

- `page` (default `1`, `integer|min:1`)
- `per_page` (default `25`, enforce `max:200`)
- `sort_by` (whitelist fields only)
- `sort_dir` (`asc|desc`)
- `q` (free text search)
- Typed filters like `type[]`, `priority[]`, `owner[]`
- Domain-specific filters (e.g., date range `start_date`, `end_date`)

Cursor endpoints should accept:

- `cursor` (previous `next_cursor` value)
- `limit`
- Same sort/filter inputs as above (sorting must match the cursor column order)

## Response Envelopes

Always wrap responses with a standard meta payload.

### Offset example

```json
{
  "data": [/* results */],
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 137,
    "total_pages": 6,
    "sort_by": "impact_score",
    "sort_dir": "desc",
    "q": "hreflang",
    "filters": {
      "type": ["Issue", "Warning"],
      "priority": ["High"],
      "owner": ["platform"]
    }
  }
}
```

### Cursor example

```json
{
  "data": [/* results */],
  "page_info": {
    "next_cursor": "eyJpZCI6NDI...0",
    "has_next_page": true,
    "sort_by": "created_at",
    "sort_dir": "desc",
    "filters": {...}
  }
}
```

## Sorting & Filtering Rules

- Only allow `sort_by` values from an explicit whitelist (`match`/`in` rule).
- Sort using the stored numeric/text fields (not formatted display strings).
- Keep units consistent across storage and response (e.g., `percent_of_total` should not be sent as 0–100 in one place and 0–1 elsewhere).
- For custom ordering (e.g., priority enums), use `FIELD(...)` or enum casting in the query layer.
- Free-text search should wrap multiple fields in one `where(function () { ... })` block.
- Filters should accept arrays (`type[]`, `priority[]`) and map cleanly to indexed columns.

## Implementation Checklist

1. Validate query params in a Form Request (or controller) with sensible defaults.
2. Build an efficient query:
   - Join/subquery only what you need for sorting/filtering.
   - Use `->paginate()` / `->simplePaginate()` / `->cursorPaginate()` accordingly.
   - Fetch heavy child data separately (e.g., time-series per row).
3. Return the standard envelope (`meta` or `page_info`).
4. Index columns used in `WHERE`, `ORDER BY`, and join conditions.
5. Cap `per_page` to protect the database (default `25`, max `200`).
6. Reset `page=1` whenever search or filters are changed on the client.
7. Include pagination metadata in automated tests to assert behaviour (`assertJsonPath('meta.total', ...)`).

## Frontend Expectations

- Keep pagination/search/sort state in the URL (query string) for deep-linking.
- Debounce free-text input (250–400 ms) before issuing requests.
- For data tables, fetch on change of page/sort/filter, and prefetch next page when appropriate.
- Consider lazy-loading detail payloads (e.g., open row → fetch `/issues/{key}/series`).

## Further Reading

- Laravel docs: `paginate()`, `simplePaginate()`, `cursorPaginate()`
- Team discussions: use `/ai/workflows/` for complex pagination decisions.
