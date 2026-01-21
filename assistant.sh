#!/bin/bash
assist() {
    cat development_reference.md | gemini "$1"
}
