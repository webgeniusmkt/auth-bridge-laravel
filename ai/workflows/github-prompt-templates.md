# Common AI Workflow Prompts for GitHub Issues & Projects

## Starting a Task
```
I'm ready to work. Please:
1. List my assigned GitHub issues in the Todo column
2. Show full details for issue #[ID]
3. Create a feature branch for it
4. Move the issue to In Progress
5. Summarize what needs to be built
```

## During Development
```
I've made changes for issue #123. Please:
1. Review what I've changed
2. Commit with proper GitHub issue reference
3. Add a progress comment to the GitHub issue
```

## Completing a Task
```
Issue #123 is complete. Please:
1. Review all changes
2. Create a PR with gh cli using proper format
3. Add technical summary to the GitHub issue
4. Move the issue/project status to In Review
```

## Code Review Response
```
I've addressed review comments on PR #45. Please:
1. Commit changes referencing issue #123
2. Push to branch
3. Add a comment to GitHub issue #123 noting the updates
```

## Emergency Hotfix
```
Critical bug in production. Please:
1. Create GitHub issue with `priority: high`
2. Create hotfix branch from main
3. [describe fix needed]
4. Fast-track PR creation and tag reviewers
```

## Key Differences from Previous Approach

| Before | With GitHub Issues + GitHub CLI |
|--------|-------------------------------|
| AI drafted commit messages manually | AI **runs** `git` commands via `gh` |
| Summaries copied into trackers | AI **comments** directly on GitHub issues |
| Status updated in third-party tool | AI **moves** project cards and labels in GitHub |
| PRs opened via web UI | AI **creates** PRs using `gh pr create` |

## Testing the Workflow

Try this prompt to verify everything works:
```
Please test our GitHub workflow:
1. Use `gh issue view 123` to fetch details
2. Show me the issue status and description
3. Don't make any changes yet, just confirm you can access GitHub Issues and the GitHub CLI

This is a test to verify the integration works.
```
