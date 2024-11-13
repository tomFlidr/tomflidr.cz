namespace Core.Translators {
	enum TranslationsBase {
		'Error'
	}
	export const Translations = Core.Translators.EnumTransform(TranslationsBase);
}