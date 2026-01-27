#!/bin/bash
# progress_assistant.sh - Smart progress tracking assistant

progress() {
    sleep 10
    
    {
        # Current progress
        echo "📋 DEVELOPMENT PROGRESS SNAPSHOT:"
        echo "===================================="
        [ -f "DEVELOPMENT_PROGRESS.md" ] && cat DEVELOPMENT_PROGRESS.md || echo "No progress file"
        
        # Project standards
        echo -e "\n📚 PROJECT STANDARDS:"
        echo "===================="
        [ -f "development_reference.md" ] && head -80 development_reference.md || echo "No reference file"
        
        # Optional file context
        if [ -n "$2" ] && [ -f "$2" ]; then
            echo -e "\n📄 FILE CONTEXT ($2):"
            echo "========================"
            head -150 "$2"
        fi
        
        # Question
        echo -e "\n❓ QUESTION/TASK:"
        echo "================"
        echo "$1"
        
    } | gemini
}