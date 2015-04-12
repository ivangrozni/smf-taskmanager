#!/bin/bash
# Program, ki bo poiskal vse patterne v enem fajlu in nato pogledal,
# ce se nahajajo v drugem fajlu
# pomembno za iskanje prevodov v modification.xml


PATTERN="\$txt\['[a-zA-Z0-9_]'\]"

for i in $(grep $PATTERN $1 -o); do
    grep $i $2
