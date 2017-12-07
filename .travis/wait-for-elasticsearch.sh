#!/usr/bin/env bash

# the script was taken form https://github.com/php-enqueue/enqueue-dev/blob/4cdaf222e8613ba7b16c1b5d4820afd4ccb73a5a/bin/test#L1
# wait for service
# $1 host
# $2 port
# $3 attempts

FORCE_EXIT=false

function waitForService()
{
    ATTEMPTS=0
    until nc -z $1 $2; do
        printf "wait for service %s:%s\n" $1 $2
        ((ATTEMPTS++))
        if [ $ATTEMPTS -ge $3 ]; then
            printf "service is not running %s:%s\n" $1 $2
            exit 1
        fi
        if [ "$FORCE_EXIT" = true ]; then
            exit;
        fi

        sleep 1
    done

    printf "service is online %s:%s\n" $1 $2
}

trap "FORCE_EXIT=true" SIGTERM SIGINT

waitForService localhost 9200 30
