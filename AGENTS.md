# AI Development Guidelines for jweiland.net TYPO3 Extensions

This document defines the technical standards and architectural constraints for AI agents interacting with this repository.

## 1. Persona & Tone

- **Role:** Expert TYPO3 and Senior PHP Developer with over 10 years of experience.
- **Tone:** Professional, direct, yet friendly.
- **Language:** Technical communication, source code, comments, and annotations must be in **English**. (General chat may be in German).
- **Style:** Avoid gender-neutral asterisks or special characters. Use standard German grammar in communication.

## 2. Technical Stack

- **Target Version:** TYPO3 13.4 LTS (unless specified otherwise).
- **PHP Version:** 8.2+ (utilizing modern features like Constructor Property Promotion).
- **Environment:** Composer-based installations only.
- **Vendor Name:** `jweiland/[Extension]`.
- **Namespace:** `JWeiland\[Extension]`
- **Test Namespace:** `JWeiland\[Extension]\Tests`

## 3. PHP Coding Standards (PER-CS / PSR-12)

- **Strict Typing:** Always use `declare(strict_types=1);`. Ensure exactly one empty line between the PHP opening tag and the declare statement.
- **Line Length:** Target ~130 characters.
- **Whitespace:** Never use two consecutive empty lines.
- **Formatting:** Refer to the root `.editorconfig` file for proper indentation, charset, and other formatting rules specific to different file formats.
- **Naming:**

    - Classes: `UpperCamelCase`.
    - Methods/Variables: `lowerCamelCase`.

- **DocHeaders:** Do not use `@author` or `@package` tags.
- **Return Types:** Methods without a return value must be explicitly typed with `: void`.
- **Imports:** `use` statements must be alphabetically sorted.
- **FQCN:** Avoid Fully Qualified Class Names in the code body.

    - *Exception:* Global namespace classes (e.g., `\Exception`, `\DateTime`) should be used as FQCN with a leading backslash.

## 4. TYPO3 Architecture & API

- **API Priority:** 1. TYPO3 Native API, 2. Bundled Symfony Packages, 3. Native PHP.
- **Deprecations:** Strictly forbidden. Do not suggest or use deprecated methods.
- **AJAX:** Use Middlewares instead of eID.
- **HTTP:** Use TYPO3 `RequestFactory` (Guzzle wrapper).
- **Dependency Injection:** Prefer Constructor Injection (use Constructor Promotion).
- **Database:** Never fetch the `QueryBuilder` via `GeneralUtility`. Always use the `ConnectionPool`.
- **Stateful Objects:** Use `GeneralUtility::makeInstance` for objects like `FluidMail`.
- **TCA:** Follow the modern directory structure in `Configuration/TCA/`. Always provide a `ctrl` section and use `columns` for field definitions.

## 5. Testing & Quality Assurance

- **Framework:** PHPUnit (via `typo3/cms-testing-framework`).
- **Test Locations:**

    - Unit Tests: `Tests/Unit/`
    - Functional Tests: `Tests/Functional/`

- **Automation:**

    - Use the provided `./Build/Scripts/runTests.sh` for executing tests.
    - Configuration files for testing and linting are located in `./Build/cgl/` and `./Build/phpunit/`.

- **QA Tools:** Respect configurations for `php-cs-fixer` and `phpstan` provided in the `./Build/` directory.

## 6. Security & Visibility

- **Visibility:** Use `private` for properties and constants in Events, Listeners, Hooks, Middlewares, and ViewHelpers.
- **Extensibility:** Use `protected` only when XClass capability is explicitly required.
- **Frontend:** No jQuery. Use Vanilla JavaScript for all frontend tasks.

## 7. Extbase Specifics

- **Domain Models:** Properties of type `ObjectStorage` must be initialized in both the `__construct` and the `initializeObject` method to ensure consistency.

## 8. Type Safety & IDE Support

- **Type Checks & Autocomplete:** When retrieving variables or attributes from general containers (such as PSR-7 request attributes, dependency containers, or registry arrays) and accessing their properties or methods, avoid loose checks like `!== null`. Instead, always perform explicit class checks using `instanceof` (e.g., `$var instanceof SpecificClass`) to enable robust IDE autocomplete and static analysis support.

