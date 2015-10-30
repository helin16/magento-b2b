BASEDIR=$(dirname $0)

echo Directory: $BASEDIR

echo clear $BASEDIR/../../../web/assets/*
rm -rf $BASEDIR/../../../web/assets/* 
echo clear $BASEDIR/../../../web/protected/runtime/*
rm -rf $BASEDIR/../../../web/protected/runtime/*

echo done
