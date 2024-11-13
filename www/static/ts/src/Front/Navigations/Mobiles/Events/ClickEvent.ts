namespace Front.Navigations.Mobiles.Events {
	export class ClickEvent extends Base {
		protected href: string;
		public constructor (href: string) {
			super();
			this.href = href;
		}
	}
}