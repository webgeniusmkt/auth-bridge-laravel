# Codacy: Check Coverage & Fix Issues

This workflow consolidates coverage checking and local analysis into one flow.

---

## When files are already provided as context

1. **Coverage Check**
   - For each file in context, run the `codacy_get_file_coverage` tool.
   - If any file is missing or below required coverage, propose and apply fixes for that file.

2. **Issue Analysis**
   - For each file in context, run the `codacy_cli_analyze` tool with:
     - `rootPath`: set to the workspace path
     - `file`: the path to the file
     - `tool`: leave empty or unset
   - If issues are found, propose and apply fixes for them.

3. **False Positives**
   - If Codacy applies a tool that shouldn't be used for this project, **do not** try to adjust Codacy configuration here. Inform the user it’s a false positive.

4. **Summarize**
   - Report what was checked, what was fixed, and any files that still need attention (coverage or issues).

---

## If no files are provided as context

1. **Ask for Targets**
   - Ask the user which file(s) they want to analyze for coverage and issues.

2. **Coverage Check**
   - For each file they specify, run `codacy_get_file_coverage`.
   - If any file is missing or below required coverage, propose and apply fixes.

3. **Issue Analysis**
   - For each file, run `codacy_cli_analyze` with:
     - `rootPath`: set to the workspace path
     - `file`: the path to the file
     - `tool`: leave empty or unset
   - If issues are found, propose and apply fixes.

4. **False Positives**
   - If Codacy applies an irrelevant tool, don’t try to configure Codacy here—tell the user it’s a false positive.

5. **Summarize**
   - Report what was checked, what was fixed, and any remaining follow-ups.

---

## Notes & Optional Improvements

### Coverage Thresholds (Optional)
- If your project follows a specific threshold (e.g., 80%), mention it in the summary and flag any file under it.

### Fix Strategy (Optional)
- For missing coverage:
  - Prefer adding or extending unit tests in the nearest test suite.
  - If tests can’t be added in this pass, mark the file for follow-up with a short rationale.

### Auto-Fix Limits (Optional)
- Only apply safe, non-breaking changes automatically.
- For large refactors, create a short plan in the summary instead of applying changes.

### Output Expectations (Optional)
- Provide a final table-like summary per file with:
  - Coverage before → after (if changed)
  - Number of issues before → after
  - Fixes applied (bulleted)
  - Remaining items / false positives

### Idempotency (Optional)
- If the workflow is run again on the same files, it should:
  - Skip files that already meet coverage and have no issues.
  - Only re-run analysis on files modified since the last run (if detectable).

### Scope Control (Optional)
- If users provide a directory, iterate through files that match your project’s language(s) and ignore generated/build outputs.

---
