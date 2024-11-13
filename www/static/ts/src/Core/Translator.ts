namespace Core {
	export class Translator {
		public Static: typeof Translator;
		protected static instance: Translator = null;
		protected static unknownKeyMark: string = '';
		protected localization: string | null;
		protected store: Map<string, Core.Translators.Record> = new Map<string, Core.Translators.Record>();
		protected devEnv: boolean = null;
		public static GetInstance (localization: string | null = null): Translator {
			if (Translator.instance == null) 
				Translator.instance = new Translator(localization);
			return Translator.instance;
		}
		public static AddBasicStore (store: object): typeof Translator {
			this.GetInstance().AddBasicStore(store);
			return this;
		}
		public static AddIcuStore (store: Map<string, Core.Translators.Record>): typeof Translator {
			this.GetInstance().AddIcuStore(store);
			return this;
		}
		public static SetUnknownKeyMark (mark: string): typeof Translator {
			this.unknownKeyMark = mark;
			return this;
		}
		public constructor (localization: string | null = null) {
			this.Static = new.target;
			this.localization = localization;
		}
		public AddBasicStore (store: object): this {
			return this.addToStore(store, 0);
		}
		public AddIcuStore (store: object): this {
			return this.addToStore(store, 1);
		}
		protected addToStore (store: object, icu: number): this {
			var icuStore = new Map<string, Core.Translators.Record>();
			for (var key in store)
				icuStore.set(key, [store[key], icu]);
			this.store = new Map<string, Core.Translators.Record>([
				...this.store, ...icuStore
			])
			return this;
		}
		public Translate (key: string, replacements?: Core.Translators.Replacements): string {
			var i18nIcu = 0, translation = '';
			if (this.store.has(key)) {
				var [translation, i18nIcu] = this.store.get(key);
			} else {
				translation = this.Static.unknownKeyMark.length > 0
					? this.Static.unknownKeyMark + key
					: key ;
			}
			return this.translateReplacements(key, translation, i18nIcu, replacements);
		}
		protected translateReplacements (key: string, translation: string, i18nIcu: number, replacements?: Core.Translators.Replacements): string {
			var translatedValue: string;
			if (i18nIcu) {
				if (IntlMessageFormat?.IntlMessageFormat == null)
					throw new Error("I18n ICU translations not supported in front application module.");
				var msgFormatter = new IntlMessageFormat.IntlMessageFormat(translation, this.localization);
				translatedValue = String(msgFormatter.format(replacements as any));
			} else {
				translatedValue = translation;
				if (replacements != null) {
					var replKeys = Object.keys(replacements),
						replKey: string,
						replRegExp: RegExp,
						replVal: string | number;
					for (var i = 0, l = replKeys.length; i < l; i++) {
						replKey = replKeys[i];
						replVal = replacements[replKey];
						replRegExp = new RegExp('\\{' + replKey + '\\}', 'g');
						translatedValue = translatedValue.replace(replRegExp, String(replVal));
					}
				}
			}
			return translatedValue;
		}
	}
}
(function(){
	var localization = document.documentElement.lang,
		langAndLocale = localization.split('-'),
		locale = langAndLocale[langAndLocale.length - 1],
		translator = Core.Translator.GetInstance(locale);
	window['t'] = Core.Translator.prototype.Translate.bind(translator);
})();