# Flaconi Behat Contexts

[![Latest version](https://img.shields.io/packagist/v/flaconi/behat-contexts.svg?style=flat-square&colorB=007EC6)](https://packagist.org/packages/flaconi/behat-contexts)
[![Downloads](https://img.shields.io/packagist/dt/flaconi/behat-contexts.svg?style=flat-square&colorB=007EC6)](https://packagist.org/packages/flaconi/behat-contexts)
[![Travis build status](https://img.shields.io/travis/Flaconi/behat-contexts/master.svg?label=travis&style=flat-square)](https://travis-ci.org/Flaconi/behat-contexts)
![PHPStan](https://img.shields.io/badge/style-level%207-brightgreen.svg?style=flat-square&label=phpstan)

Behat extension with custom helper steps

* Test - improving the code style of phpunit test cases

## Table of contents

1. [Installation](#installation)
2. [Enable this extension and configure Behat to use it](#enable-this-extension-and-configure-behat-to-use-it)
3. [Contributing](#contributing)

## Installation

The recommended way to install Flaconi Behat Contexts is [through Composer](http://getcomposer.org).

```JSON
{
	"require-dev": {
		"flaconi/behat-contexts": "^1.0"
	}
}
```

## Enable this extension and configure Behat to use it
    ```yaml
    # behat.yml
    default:
        # ...
        extensions:
            Flaconi\Behat\Extension: ~
    ```

## Contributing

To make this repository work on your machine, clone it and run these three commands in the root directory of the repository:

```
composer install
composer code-style
composer tests-ci
```

After writing some code and editing or adding feature tests, run these commands again to check that everything is OK:

```
composer code-style
composer tests-ci
```

We are always looking forward for your bugreports, feature requests and pull requests. Thank you.
