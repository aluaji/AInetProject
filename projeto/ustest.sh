#!/bin/bash

if [ $# -ne 2 ]; then
    echo "Usage $0 <test number> <method_regex_pattern>"
    echo "Example:"
    echo "$0 2 a_register_route_exists"
    exit 1
fi

if [[ $1 =~ ^[0-9]$ ]]; then
    TESTSUITE=$(printf 'UserStory0%sTest' $1)
else
    TESTSUITE=$(printf 'UserStory%sTest' $(echo $1 | tr [a-z] [A-Z]))
fi

vendor/bin/phpunit --filter "$TESTSUITE::$2" --testdox
