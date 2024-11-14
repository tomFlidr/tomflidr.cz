namespace Front {
	export class Page extends Core.Page {
		public static readonly SELECTORS = Front.Pages.Selectors;
		public Static: typeof Front.Page;
		protected mobileNavigation: Front.Navigations.Mobile;
		constructor () {
			super();
			this.initWindowScroll();
			var layout = this.Static.GetLayout(),
				mediaVersion = this.Static.GetMediaSiteVersion();
			if (layout.IsStandard() && mediaVersion.IsMobile()) {
				this.mobileNavigation = new Front.Navigations.Mobile(this);
			}
		}
		public HandleDocumentReady (): void {
			super.HandleDocumentReady();
		}
		protected initWindowScroll (): this {
			window.addEventListener('scroll', this.handleWindowScroll.bind(this));
			this.handleWindowScroll();
			return this;
		}
		protected handleWindowScroll (): void {
			var cls = this.Static.SELECTORS.SCROLLED_CLS,
				body = document.body;
			if (window.scrollY > 0) {
				if (!body.hasClass(cls))
					body.addClass(cls);
			} else {
				body.removeClass(cls);
			}
		}
	}
}

// run all declared javascripts after <body>, after all elements are declared
Core.Page.SetPageType(Front.Page);
