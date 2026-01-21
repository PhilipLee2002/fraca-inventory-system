#!/bin/bash
assist() {
    {
        echo "=== PROJECT REFERENCE ==="
        cat development_reference.md
        echo -e "\n=== CURRENT PROJECT STRUCTURE ==="
        find . -type f -name "*.php" -o -name "*.js" -o -name "*.blade.php" | head -20
        echo -e "\n=== RELEVANT CODE FILES ==="
        if [ -f "$2" ]; then
            echo "File: $2"
            cat "$2"
        fi
        echo -e "\n=== USER QUESTION ==="
        echo "$1"
    } | gemini
}
