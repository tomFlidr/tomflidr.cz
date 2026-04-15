# Contributing

Thank you for your interest in this project.

This is a personal website repository. Contributions are welcome in the form
of bug reports, typo fixes or suggestions, but the project is not intended for
collaborative feature development.

## Reporting issues

Use [GitHub Issues](../../issues) to report:

- Broken links or missing content
- Rendering bugs across browsers or screen sizes
- Typos or factual errors in any language version (cs / en / de)
- Security vulnerabilities (see [SECURITY.md](SECURITY.md))

Please include:

- Browser / OS version (for rendering issues)
- Steps to reproduce
- Expected vs. actual behaviour
- A screenshot if relevant

## Submitting a pull request

1. Fork the repository and create a branch from `main`.
2. Keep changes focused — one logical fix per pull request.
3. Follow the existing code style (tabs for indentation, English comments).
4. Test locally before opening the PR (`php >= 8.2`, Apache with `mod_rewrite`, Redis).
5. Describe what changed and why in the PR description.

## Code style

| Language   | Style notes                                         |
|------------|-----------------------------------------------------|
| PHP        | PSR-12, tabs, OOP, no unnecessary dependencies      |
| TypeScript | ES5 target, tabs, no external runtime dependencies  |
| CSS        | Plain CSS, no preprocessor, tabs                    |
| XML/Latte  | 2-space indent inside XML content nodes             |

## License

By submitting a contribution you agree that your changes will be licensed
under the [BSD 3-Clause License](../LICENSE.md) that covers this project.
