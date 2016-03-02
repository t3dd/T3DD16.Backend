#!/bin/bash

pushd $(dirname $0) > /dev/null

export typo3DatabaseName="t3_t3dd16_dev";
export typo3DatabaseHost="127.0.0.1";
export typo3DatabaseUsername="development";
export typo3DatabasePassword="development";

../bin/phpunit --color -c UnitTests.xml $1
../bin/phpunit --color -c FunctionalTests.xml $1

popd > /dev/null