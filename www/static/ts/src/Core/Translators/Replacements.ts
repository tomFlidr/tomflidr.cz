namespace Core.Translators {
	export type Replacements = {
		[replacementKey: string]: string | number
	} | (string | number)[]
}