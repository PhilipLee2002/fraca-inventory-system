#!/bin/bash
ask_fraca() {
    cat development_reference.md | gemini "$1"
}
get_phase() {
    cat development_reference.md | gemini "Phase $1 details:"
}
fraca_help() {
    echo "ask_fraca 'question'"
    echo "get_phase [number]"
}
