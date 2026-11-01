---
id: be9d6488-ac14-4814-a861-8daa1adff69d
blueprint: nested
title: Nested
summary: 'Both fieldtypes inside a Replicator set and a Grid row, where field and meta paths are prefixed.'
template: tests/nested
blocks:
  -
    id: n1aaaaaa
    type: section
    enabled: true
    classes:
      -
        id: n1cccccc
        padding: py-8
        align: text-center
      -
        id: n1dddddd
        padding: py-16
        variant: lg
    options:
      theme: chalk
      visible: true
rows:
  -
    id: n2aaaaaa
    label: 'First row'
    classes:
      -
        id: n2cccccc
        colour: text-sky-700
      -
        id: n2dddddd
        colour: text-amber-700
        variant: dark
    options:
      size: lg
      badge: 'Open late'
---
