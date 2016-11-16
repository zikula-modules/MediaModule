#!/usr/bin/env bash
# Based on
# https://github.com/steveklabnik/automatically_update_github_pages_with_travis_example

set -o errexit -o nounset

if [ "$TRAVIS_BRANCH" != "master" ]
then
  echo "This commit was made against the $TRAVIS_BRANCH and not the master! No deploy!"
  exit 0
fi

rev=$(git rev-parse --short HEAD)

mkdir tmp-deploy
cp ../MediaModule.zip tmp-deploy/MediaModule.zip
cd tmp-deploy

git init
git config user.name "Travis CI"
git config user.email "travis@christianflach.de"

git remote add upstream "https://$GH_TOKEN@github.com/cmfcmf/MediaModule.git"
git checkout -b dev-builds
git add .
git commit -m "Deploy current master version (${rev})."
git push -f --set-upstream upstream dev-builds

cd ..
rm -Rf tmp-deploy
