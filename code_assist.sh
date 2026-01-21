#!/bin/bash
code_assist() {
    {
        echo "REFERENCE DOC:"
        cat development_reference.md
        echo -e "\nCODE FILE ($2):"
        [ -f "$2" ] && cat "$2" || echo "File not found"
        echo -e "\nQUESTION:"
        echo "$1"
    } | gemini
}
