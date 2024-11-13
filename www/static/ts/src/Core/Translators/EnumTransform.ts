namespace Core.Translators {
	export function EnumTransform <T extends object>(constTransKeysEnum: T) {
		const keys = Object.keys(constTransKeysEnum).filter((v) => isNaN(Number(v))) as Array<keyof T>;
		const result = {} as {
			[K in keyof T]: K
		};
		for (const key of keys)
			result[key] = key;
		return result;
	}
}