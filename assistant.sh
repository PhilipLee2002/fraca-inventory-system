#!/bin/bash
assist() {
    sleep 10
    cat development_reference.md | gemini "$1"
}
