namespace Front.Navigations.Mobiles {
	export class Elements {
		public static readonly SELECTORS = {
			CONT_ELM_ID: 'left-panel',
			OPEN_BTN_ID: 'left-panel-open',
			CLOSE_BTN_ID: 'left-panel-close',
			OPENED_CLS: 'left-panel-opened',
		};
		protected Static: typeof Mobiles.Elements;
		protected main: Mobile;
		protected members: Mobiles.Interfaces.IMembers | null = null;
		protected cssAnimDuration: number;
		protected cssAnimationTimeout: number | null;
		public constructor (main: Mobile) {
			this.Static = new.target;
			this.main = main;
			this.initElements();
		}
		public GetMembers (): Mobiles.Interfaces.IMembers {
			return this.members;
		}
		public Show (): this {
			var sels = this.Static.SELECTORS;
			this.members.Container.removeClass(sels.OPENED_CLS);
			this.members.Body.addClass(sels.OPENED_CLS);
			clearTimeout(this.cssAnimationTimeout);
			this.cssAnimationTimeout = setTimeout(() => {
				this.members.Container.addClass(sels.OPENED_CLS);
			}, this.cssAnimDuration);
			return this;
		}
		public Hide (): this {
			var sels = this.Static.SELECTORS;
			this.members.Container.removeClass(sels.OPENED_CLS);
			this.members.Body.removeClass(sels.OPENED_CLS);
			this.members.CloseBtn.blur();
			clearTimeout(this.cssAnimationTimeout);
			this.cssAnimationTimeout = setTimeout(() => {
				this.members.Container.addClass(sels.OPENED_CLS);
			}, this.cssAnimDuration);
			return this;
		}
		protected initElements (): boolean {
			var sels = this.Static.SELECTORS,
				contElm: HTMLDivElement = this.getById<HTMLDivElement>(sels.CONT_ELM_ID);
			if (!contElm) throw new Error(
				`Left panel container not found, id: '${sels.CONT_ELM_ID}'.`
			);
			this.members = <Mobiles.Interfaces.IMembers>{
				Body: document.body,
				Container: contElm,
				OpenBtn: this.getById<HTMLAnchorElement>(sels.OPEN_BTN_ID),
				CloseBtn: this.getById<HTMLAnchorElement>(sels.CLOSE_BTN_ID)
			};
			var cssAnimDurationsRaw = contElm.getStyleAlt('transition-duration');
			if (cssAnimDurationsRaw == null) {
				this.cssAnimDuration = 1000;
			} else {
				var cssAnimDurationsRawArr = cssAnimDurationsRaw.split(','),
					cssAnimDurations: number[] = [],
					cssAnimDuration: number | null;
				for (var cssAnimDurationRaw of cssAnimDurationsRawArr) {
					cssAnimDuration = Number.parseFloatSafe(cssAnimDurationRaw);
					if (cssAnimDuration != null) {
						cssAnimDurations.push(cssAnimDuration);
					}
				}
				this.cssAnimDuration = Math.max(...cssAnimDurations) * 1000;
			}
			return true;
		}
		protected getById <T extends HTMLElement>(elmId: string): T {
			return document.getElementById(elmId) as T;
		}
	}
}