#!/bin/bash
USER_ID=$(stat -c "%u" /home/arc2/code)
GROUP_ID=$(stat -c "%g" /home/arc2/code)

usermod -u $USER_ID arc2
groupmod -g $GROUP_ID arc2

su arc2 -c "git config --global --add safe.directory /home/arc2/code"

exec tail -f /dev/null
