import * as IntlMFAll from "intl-messageformat";
import { ParserOptions } from '@formatjs/icu-messageformat-parser';

export {}

declare global {
	const t: (key: string, replacements?: Core.Translators.Replacements) => string;
	namespace IntlMessageFormat {
		interface ParserOpts extends ParserOptions {}
	}
	const IntlMessageFormat: typeof IntlMFAll;
}