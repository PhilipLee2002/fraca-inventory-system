# Gemini CLI Assistant — Custom Function Calls Cheatsheet

**Quick Reference Guide for Custom Gemini CLI Functions in the FRACA Inventory System**

---

## Overview

This cheatsheet documents the custom shell functions created for the FRACA SERVCOM Inventory Management System. These functions provide quick shortcuts to common AI-assisted development tasks, leveraging Gemini CLI with project documentation and code context.

**All functions use `gemini` CLI command with context injection from project files.**

---

## Custom Shell Functions

### 1. assist() — General Code Assistance
**File:** `assistant.sh`

```bash
assist "question about the code"
```

**Purpose:** Get AI assistance with project code using development reference as context

**What it does:**
- Waits 10 seconds for terminal to be ready
- Pipes development_reference.md to Gemini
- Answers questions about project structure and requirements
- Includes full project standards and feature list

**Examples:**
```bash
assist "How do I create a new product controller?"
assist "What are the database relationships for Product model?"
assist "Explain the role-based access control implementation"
```

**Output:** AI response with project-aware guidance

---

### 2. code_assist() — Contextual Code Assistance
**File:** `code_assist.sh`

```bash
code_assist "your question" path/to/file.php
```

**Purpose:** Get AI help with specific code file using project context

**What it does:**
- Waits 10 seconds for terminal
- Sends to Gemini:
  1. Project reference documentation
  2. The specific file content (if exists)
  3. Your question
- Returns AI analysis with file context

**Parameters:**
- `$1` — Your question/task
- `$2` — File path to analyze

**Examples:**
```bash
code_assist "How do I add a new relationship?" app/Models/Product.php
code_assist "What validation is missing?" app/Http/Controllers/UserController.php
code_assist "Fix the bug in this file" app/Http/Middleware/CheckRole.php
```

**Output:** AI response considering both project standards and file content

---

### 3. ask_fraca() — Project-Specific Questions
**File:** `fraca_helper.sh`

```bash
ask_fraca "question about FRACA system"
```

**Purpose:** Ask questions specifically about the inventory system requirements

**What it does:**
- Waits 10 seconds
- Pipes development_reference.md to Gemini
- Answers FRACA-specific feature and requirement questions

**Examples:**
```bash
ask_fraca "What features are in Phase 3?"
ask_fraca "How should stock history tracking work?"
ask_fraca "What are the alert system requirements?"
```

**Output:** Detailed answer about FRACA system requirements

---

### 4. get_phase() — Get Phase Details
**File:** `fraca_helper.sh`

```bash
get_phase [phase_number]
```

**Purpose:** Retrieve detailed information about a specific development phase

**What it does:**
- Queries development_reference.md for specific phase
- Returns phase objectives, deliverables, and tasks

**Parameters:**
- `[phase_number]` — Phase number (0, 1, 2, 3, etc.)

**Examples:**
```bash
get_phase 0
get_phase 1
get_phase 3
```

**Output:** Complete phase description with all requirements

---

### 5. fraca_help() — Function Reference
**File:** `fraca_helper.sh`

```bash
fraca_help
```

**Purpose:** Display available FRACA helper functions

**What it does:**
- Shows usage syntax for ask_fraca and get_phase
- Provides quick reference

**Output:**
```
ask_fraca 'question'
get_phase [number]
```

---

### 6. progress() — Smart Progress Assistant
**File:** `progress_assist.sh`

```bash
progress "your task or question" [optional: path/to/file.php]
```

**Purpose:** Get AI assistance with contextual awareness of current development progress

**What it does:**
- Waits 10 seconds
- Sends to Gemini:
  1. Full DEVELOPMENT_PROGRESS.md snapshot (what's been done)
  2. Project standards from development_reference.md
  3. Optional file context (first 150 lines)
  4. Your question/task
- Returns progress-aware recommendations

**Parameters:**
- `$1` — Your question/task (required)
- `$2` — File path to include context (optional)

**Examples:**
```bash
progress "What should I implement next in Phase 3?"
progress "How do I implement stock history tracking?" app/Models/StockHistory.php
progress "Should I add more seeders before implementing controllers?"
progress "What tests should I write for Purchase controller?" app/Http/Controllers/PurchaseController.php
```

**Output:** AI response aware of project progress and current phase

**Special Feature:** Shows emoji-enhanced output with sections for:
- 📋 Development Progress Snapshot
- 📚 Project Standards
- 📄 File Context (if provided)
- ❓ Question/Task

---

## Typical Workflow

### Daily Development Loop

```bash
# 1. Check what's next
progress "What task should I work on today?"

# 2. Ask about implementation approach
ask_fraca "How should I implement feature X?"

# 3. Get help with specific code
code_assist "How do I add validation?" app/Http/Requests/StoreProductRequest.php

# 4. Understand phase requirements
get_phase 3

# 5. Ask about current implementation
assist "Is my approach following project standards?"

# 6. Get progress-aware guidance
progress "I've completed X, what's next?" app/Http/Controllers/NewController.php
```

---

## How to Use These Functions

### Prerequisites

1. **Gemini CLI installed:**
   ```bash
   # Install if not present
   npm install -g gemini-cli
   # or
   pip install gemini-cli
   ```

2. **Functions sourced in your shell:**
   ```bash
   # Add to ~/.bashrc or ~/.zshrc
   source /path/to/assistant.sh
   source /path/to/code_assist.sh
   source /path/to/fraca_helper.sh
   source /path/to/project_assistant.sh
   source /path/to/progress_assist.sh
   ```

### Usage Tips

**Tip 1: Source all functions at once**
```bash
# Create a setup script
for script in *.sh; do source "$script"; done
```

**Tip 2: Use progress() for decision-making**
```bash
# Before starting a feature
progress "Should I implement X or Y next?"
```

**Tip 3: Use code_assist() for specific files**
```bash
# When debugging or refactoring
code_assist "What's wrong with this code?" app/Http/Controllers/ProductController.php
```

**Tip 4: Combine functions**
```bash
# First understand the phase
get_phase 3

# Then ask about implementation
ask_fraca "Phase 3 detailed requirements"

# Then get code help
code_assist "How do I start?" app/Models/Product.php
```

---

## Function Context Injection Map

| Function | Context Injected | Use Case |
|----------|------------------|----------|
| `assist()` | development_reference.md | General project questions |
| `code_assist()` | reference + file content | Specific code analysis |
| `ask_fraca()` | development_reference.md | Feature requirements |
| `get_phase()` | development_reference.md | Phase details |
| `progress()` | progress file + reference + file | Progress-aware decisions |

---

## Real-World Examples

### Example 1: Implementing a New Feature
```bash
# 1. Check what's required
get_phase 3

# 2. Ask about the feature
ask_fraca "Stock history requirements"

# 3. Get code help
code_assist "How do I log stock changes?" app/Models/StockHistory.php

# 4. Implement and get progress update
progress "I've started StockHistoryController, what's the pattern?" app/Http/Controllers/StockHistoryController.php
```

### Example 2: Understanding Relationships
```bash
# Start with phase info
get_phase 1

# Ask about relationships
ask_fraca "What are the Product relationships?"

# Analyze the model
code_assist "How are relationships defined?" app/Models/Product.php

# Get progress context
progress "Should I add more relationships?" app/Models/Product.php
```

### Example 3: Permission System Implementation
```bash
# Understand requirements
ask_fraca "Role-based access control requirements"

# Analyze existing implementation
code_assist "How does CheckRole middleware work?" app/Http/Middleware/CheckRole.php

# Get next steps
progress "What permissions should I seed next?" database/seeders/PermissionSeeder.php
```

---

## Function Comparison

| Function | Input | Context | Best For |
|----------|-------|---------|----------|
| `assist()` | Question | Project ref | Quick questions |
| `code_assist()` | Question + File | Ref + Code | Analyzing code |
| `ask_fraca()` | Question | Project ref | Requirements |
| `get_phase()` | Phase # | Project ref | Phase details |
| `progress()` | Question + File | Progress + Ref + Code | Decision making |

**Quick Decision Guide:**
- 🤔 "What do I implement next?" → Use `progress()`
- 📝 "How do I implement X?" → Use `ask_fraca()`
- 🔍 "Why isn't this code working?" → Use `code_assist()`
- 📚 "What does Phase X include?" → Use `get_phase()`
- ❓ "General project question?" → Use `assist()`

---

## Supplementary Development Commands

While the custom functions above handle most common tasks, these standard commands are still useful:

### Laravel Commands
```bash
php artisan migrate                    # Run pending migrations
php artisan migrate:fresh              # Reset and re-run all migrations
php artisan db:seed                    # Run database seeders
php artisan serve                      # Start development server
php artisan tinker                     # Interactive shell
php artisan route:list                 # Show all routes
```

### NPM Commands
```bash
npm run dev                            # Start Vite dev server
npm run build                          # Production build
npm install                            # Install dependencies
```

### Composer Commands
```bash
composer run setup                     # Setup script
composer run dev                       # Development environment
composer run test                      # Run tests
composer install                       # Install dependencies
```

### Git Commands
```bash
git status                             # Check changes
git add .                              # Stage all
git commit -m "message"                # Commit
git push origin main                   # Push to remote
git log --oneline -10                  # View recent commits
```

---

**Last Updated:** January 27, 2026  
**Compatible With:** FRACA SERVCOM Inventory System v1.0  
**Custom Functions:** 5 (in .sh files)

**Quick Access:**
- 📋 Source all functions: `for script in *.sh; do source "$script"; done`
- 🚀 Main function: `progress()` — Most versatile for daily development
- 📚 Reference: `ask_fraca()` and `get_phase()` — For understanding requirements
- 🔍 Analysis: `code_assist()` — For code-specific help
- ❓ General: `assist()` — For quick questions
