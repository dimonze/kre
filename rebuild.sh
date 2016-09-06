#!/bin/bash
./symfony doctrine:build-model --application="frontend"
./symfony doctrine:build-form --application="frontend"
./symfony doctrine:build-filter --application="frontend"