#!/bin/zsh


# Replace class helper...

replaceClass() {
  local before="$1-$2"
  local beforeEscaped="${1//-/\-}-${2//./\.}"
  local after="jstw-$1-$3"
  rg -g '*.{html,php,vue,css,scss}' -l "$beforeEscaped" | xargs sed -i '' "s/\([^.a-zA-Z0-9_-]\)${beforeEscaped}\([^.a-zA-Z0-9_-]\)/\1${after}___REPLACED\2/g"
}


# Replace margin and padding classes...

mpPrefixes=(
  'm' 'mx' 'my' 'mb' 'mt' 'ml' 'mr'
  'p' 'px' 'py' 'pb' 'pt' 'pl' 'pr'
)

declare -A mpReplacements
mpReplacements[8]='30'
mpReplacements[7]='20'
mpReplacements[6]='16'
mpReplacements[5]='10'
mpReplacements[4]='8'
mpReplacements[3]='6'
mpReplacements[2]='4'
mpReplacements[1.5]='3'
mpReplacements[1]='2'
mpReplacements[.5]='1.5'
mpReplacements[xs]='0.5'
mpReplacements[sm]='1'

for prefix in ${mpPrefixes}; do
  for before after in ${(kv)mpReplacements}; do
    replaceClass $prefix $before $after
    replaceClass "-$prefix" $before $after
  done
done


# Replace height classes...

declare -A heightReplacements
heightReplacements[80]='55'
heightReplacements[64]='48'
heightReplacements[5]='1'
heightReplacements[3]='2'
heightReplacements[2.5]='1.5'
heightReplacements[2]='1'
heightReplacements[1]='0.5'

for before after in ${(kv)heightReplacements}; do
  replaceClass 'h' $before $after
done


# Replace min height classes...

declare -A minHeightReplacements
minHeightReplacements[48]='40'
minHeightReplacements[24]='20'

for before after in ${(kv)minHeightReplacements}; do
  replaceClass 'min-h' $before $after
done



# Remove temporary `___REPLACED` markers

rg -g '*.{html,php,vue,css,scss}' -l "___REPLACED" | xargs sed -i '' "s/___REPLACED//g"