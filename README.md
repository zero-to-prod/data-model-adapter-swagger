# Zerotoprod\DataModelAdapterSwagger

[![Repo](https://img.shields.io/badge/github-gray?logo=github)](https://github.com/zero-to-prod/data-model-adapter-swagger)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/data-model-adapter-swagger/test.yml?label=test)](https://github.com/zero-to-prod/data-model-adapter-swagger/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/data-model-adapter-swagger/backwards_compatibility.yml?label=backwards_compatibility)](https://github.com/zero-to-prod/data-model-adapter-swagger/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/zero-to-prod/data-model-adapter-swagger?color=blue)](https://packagist.org/packages/zero-to-prod/data-model-adapter-swagger/stats)
[![Packagist Version](https://img.shields.io/packagist/v/zero-to-prod/data-model-adapter-swagger?color=f28d1a)](https://packagist.org/packages/zero-to-prod/data-model-adapter-swagger)
[![License](https://img.shields.io/packagist/l/zero-to-prod/data-model-adapter-swagger?color=red)](https://github.com/zero-to-prod/data-model-adapter-swagger/blob/main/LICENSE.md)
[![wakatime](https://wakatime.com/badge/github/zero-to-prod/data-model-adapter-swagger.svg)](https://wakatime.com/badge/github/zero-to-prod/data-model-adapter-swagger)
[![Hits-of-Code](https://hitsofcode.com/github/zero-to-prod/data-model-adapter-swagger?branch=main)](https://hitsofcode.com/github/zero-to-prod/data-model-adapter-swagger/view?branch=main)

## Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Documentation Publishing](#documentation-publishing)
  - [Automatic Documentation Publishing](#automatic-documentation-publishing)
- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Contributing](#contributing)

## Introduction

Adapter for the Swagger 2.0 for [DataModelGenerator](https://github.com/zero-to-prod/data-model-generator).

## Requirements

- PHP 8.1 or higher.

## Installation

You can install this package via Composer.

```shell
composer require zero-to-prod/data-model-adapter-swagger
```

## Documentation Publishing

You can publish this README to your local documentation directory.

This can be useful for providing documentation for AI agents.

This can be done using the included script:

```bash
# Publish to default location (./docs/zero-to-prod/data-model-adapter-swagger)
vendor/bin/zero-to-prod-data-model-adapter-swagger

# Publish to custom directory
vendor/bin/zero-to-prod-data-model-adapter-swagger /path/to/your/docs
```

### Automatic Documentation Publishing

You can automatically publish documentation by adding the following to your `composer.json`:

```json
{
    "scripts": {
        "post-install-cmd": [
            "zero-to-prod-data-model-adapter-swagger"
        ],
        "post-update-cmd": [
            "zero-to-prod-data-model-adapter-swagger"
        ]
    }
}
```

## Usage

Generate components from a Swagger 2.0 schema.

```php
namespace Zerotoprod\DataModelAdapterSwagger;

$Components = Swagger::adapt(file_get_contents(__DIR__.'/schema.json'))
```

This will add the package to your project’s dependencies and create an autoloader entry for it.

## Contributing

Contributions, issues, and feature requests are welcome!
Feel free to check the [issues](https://github.com/zero-to-prod/data-model-adapter-swagger/issues) page if you want to contribute.

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit changes (`git commit -m 'Add some feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Create a new Pull Request.
