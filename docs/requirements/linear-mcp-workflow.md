# Linear MCP + GitHub CLI Workflow

## Prerequisites
- Linear MCP server configured and running
- GitHub CLI (`gh`) authenticated
- Default repository set: `gh repo set-default owner/repo`

## Starting a Task

### 1. Fetch Current Tasks
```
"Show me my assigned Linear issues that are in Todo or In Progress"
```

The AI will use Linear MCP to query your issues.

### 2. Get Issue Details
```
"Get full details for WEB-123 including description and acceptance criteria"
```

### 3. Update Status
```
"Update WEB-123 to In Progress"
```

## During Development

### Create Branch (via gh)
```bash
gh repo view  # verify correct repo
git checkout -b feature/WEB-123-user-authentication
```

### Make Changes
AI generates code following project conventions.

### Commit (via gh or git)
```bash
git add .
git commit -m "WEB-123: Add user authentication endpoint

- Created UserController with login/register methods
- Added UserStoreRequest with validation
- Created UserResource for API responses
- Added authentication routes

Linear: https://linear.app/web-genius-marketing/issue/WEB-123"
```

### Add Linear Comment (via MCP)
```
"Add a comment to WEB-123 with:
- Created UserController in app/Http/Controllers/Api/v1/
- Added validation in app/Http/Requests/User/
- Added resource transformation in app/Http/Resources/User/
- All following our API conventions"
```

## Completing Work

### 1. Push and Create PR (via gh)
```bash
git push -u origin feature/WEB-123-user-authentication

gh pr create \
  --title "WEB-123: Add user authentication endpoint" \
  --body "Closes WEB-123

## Changes
- User authentication with login/register
- Request validation 
- Resource transformation
- Following /docs/requirements/creating-api-controllers.md conventions

## Testing
\`\`\`bash
POST /api/v1/users/register
POST /api/v1/users/login
\`\`\`

Linear: https://linear.app/web-genius-marketing/issue/WEB-123" \
  --web
```

### 2. Update Linear (via MCP)
```
"Update WEB-123:
- Status: In Review
- Add comment: PR created at [PR URL]. Implemented user authentication 
  following project conventions. Ready for review."
```

### 3. After Merge
```
"Update WEB-123 status to Done and add comment: Merged to main, deployed"
```

## AI Automation Examples

### Full Task Workflow
```
"I want to work on WEB-123. Please:
1. Get the issue details from Linear
2. Create a feature branch with gh
3. Show me what needs to be done
4. Update Linear status to In Progress"
```

### Completion Workflow
```
"I've completed work on WEB-123. Please:
1. Review the changes I've made
2. Create a PR with gh using proper format
3. Add a technical summary to Linear
4. Update status to In Review"
```

## GitHub CLI Reference

### Common Commands
```bash
# Repository
gh repo view
gh repo set-default owner/repo

# Branches
gh pr create --draft  # creates branch + draft PR
gh pr ready  # mark PR as ready for review

# PRs
gh pr create --title "..." --body "..." --web
gh pr list --author @me
gh pr view 123
gh pr merge 123 --squash

# Issues (GitHub, not Linear)
gh issue list
gh issue create
```

## Linear MCP Capabilities

The AI can use Linear MCP to:
- ✅ List issues (by status, assignee, project)
- ✅ Get issue details
- ✅ Update issue status
- ✅ Add comments to issues
- ✅ Create new issues
- ✅ Update issue fields (priority, labels, etc.)
- ✅ Link issues to PRs

## Best Practices

### Commit Messages
Always include Linear issue ID in first line:
```
WEB-123: Brief imperative description (50 chars or less)

More detailed explanation (72 chars per line):
- What changed
- Why it changed
- Any implications

Linear: https://linear.app/web-genius-marketing/issue/WEB-123
```

### Linear Comments
Use structured format:
```
**Files Changed:**
- app/Http/Controllers/Api/v1/UserController.php
- app/Http/Requests/User/UserStoreRequest.php

**Decisions Made:**
- Used bcrypt for password hashing
- Email validation includes uniqueness check

**Next Steps:**
- Add tests
- Update API documentation
```

### PR Descriptions
```markdown
Closes WEB-123

## Summary
Brief description of changes

## Changes
- Bullet list of specific changes

## Testing
How to test these changes

## Documentation
Link to relevant docs or note if docs updated

Linear: [issue URL]
```

## Troubleshooting

### Linear MCP not responding
- Check MCP server is running
- Verify Linear API token is valid
- Check network connectivity

### GitHub CLI errors
```bash
gh auth status  # check authentication
gh auth login   # re-authenticate if needed
gh repo set-default  # set default repo
```