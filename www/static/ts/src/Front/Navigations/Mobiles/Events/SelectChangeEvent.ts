namespace Front.Navigations.Mobiles.Events {
	export class SelectChangeEvent extends Base {
		protected value: string;
		public constructor (value: string) {
			super();
			this.value = value;
		}
		public SetValue (value: string): this {
			this.value = value;
			return this;
		}
		public GetValue (): string {
			return this.value;
		}
	}
}