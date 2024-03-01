# Validations

Provide common validation rules

## Setup

Make sure `\FKS\Providers\ValidationServiceProvider::class` is registered in `providers` section of `config/app.php`

## Rules

### uuid_or_hex

Validate that variable is uuid or hex

### icd_code

Validate that variable has valid icd code format. Can contains numeric, A-Za-z chars and '.'. 
Maximum 7 chars without '.'. or 8 with '.'.