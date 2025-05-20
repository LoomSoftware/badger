# Loom | Badger

<p>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-1.0.0-blue" alt="Version 1.0.0">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-40adbc" alt="License GPL-3.0-or-later">
</p>

Add version badges to your project README file.

## Installation

```shell
composer require loomsoftware/badger
```

## Usage

Add tags where you'd like the badges to display in your `README.md` file, in the form of HTML comments:

```markdown
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-1.0.0-blue" alt="Version 1.0.0">
<!-- License Badge -->
<!-- Coverage Badge -->
```

Run the badge commands:

```shell
./vendor/bin/badger badge:version .
./vendor/bin/badger badge:license .
./vendor/bin/badger badge:coverage .
```