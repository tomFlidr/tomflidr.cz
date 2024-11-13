namespace Core {
	export class Layout {
		private _layouts: Map<Layouts.Name, boolean>;
		public IsStandard (): boolean {
			return this._layouts.get(Layouts.Name.Standard);
		}
		public IsPrint (): boolean {
			return this._layouts.get(Layouts.Name.Print);
		}
		public constructor (layoutName: Layouts.Name) {
			var layoutCodeNames = Object.keys(Layouts.Name);
			this._layouts = new Map<Layouts.Name, boolean>();
			for (var layoutCodeName of layoutCodeNames)
				this._layouts.set(Layouts.Name[layoutCodeName], false);
			if (this._layouts.has(layoutName)) {
				this._layouts.set(layoutName, true);
			} else {
				throw new Error(`Unknown layout: '${layoutName}'.`);
			}
		}
	};
}