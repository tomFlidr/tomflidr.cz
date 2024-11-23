namespace Front {
	export class Page extends Core.Page {
		public static readonly SELECTORS = Front.Pages.Selectors;
		public Static: typeof Front.Page;
		protected mobileNavigation: Front.Navigations.Mobile;
		constructor () {
			super();
			this.initWindowScroll();
			this.initInfoLinks();
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
		protected initInfoLinks (): void {
			var sel = this.Static.SELECTORS.INFO_LINK_SEL,
				infoLinks = Array.from<HTMLElement>(document.querySelectorAll(sel));
			for (var infoLink of infoLinks) {
				infoLink.addEventListener('click', this.handleInfoLinkClick.bind(this, infoLink));
			}
		}
		protected handleInfoLinkClick (infoLink: HTMLElement, e: MouseEvent): void {
			var url = infoLink.dataset.href,
				target = infoLink.dataset.target;
			if (target != null) {
				window.open(url, target);
			} else {
				location.href = url;
			}
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
