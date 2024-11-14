namespace Core {
	export class Page {
		public static readonly SELECTORS = Core.Pages.Selectors;
		public Static: typeof Core.Page;
		protected static config: Core.Configs.IConfig = null;
		private static _docReadyInit: number = 0;
		private static _docReadyHandlers: Function[] = [];
		private static _pageType: typeof Core.Page;
		private static _page: Core.Page;
		private static _pageCreationAdded: boolean = false;
		private static _coreFeaturesInit: boolean = false;
		/** @summary Get or create singleton page instance if necessary and returns it. */
		public static GetPage (): Core.Page {
			var ctx = Core.Page;
			if (ctx._page == null) {
				ctx._page = new ctx._pageType();
			}
			return ctx._page;
		}
		/** @summary Set singleton class instance type to initialize after dom ready. Any previous class types will be overwritten. */
		public static SetPageType (pageType: typeof Core.Page): typeof Core.Page {
			var ctx = Core.Page;
			ctx._pageType = pageType;
			if (ctx._pageCreationAdded) 
				return this;
			ctx._pageCreationAdded = true;
			ctx.AddDocReadyHandler(() => {
				ctx.GetPage().HandleDocumentReady();
			});
			return this;
		}
		/** @summary Add any handler after document.readyState == 'interactive' event (or run handler immediately if current moment is after the event). */
		public static AddDocReadyHandler (handler: () => void): typeof Core.Page {
			var ctx = Core.Page;
			ctx._docReadyHandlers.push(handler);
			if (ctx._docReadyInit === 3) {
				handler();
				return this;
			}
			if (ctx._docReadyInit === 2) 
				return this;
			ctx._docReadyInit = 1;
			document.addEventListener('readystatechange', this.handleReadyStateChange.bind(this));
			return this;
		}
		/** @summary Called after document interactive ready state */
		public HandleDocumentReady (): void {
			document.body.addClass(this.Static.SELECTORS.DOC_READY_CLS);
		}
		protected static handleReadyStateChange (ev: Event): void {
			var ctx = Core.Page;
			if (document.readyState !== 'interactive' || Core.Page._docReadyInit >= 2) 
				return;
			Core.Page._docReadyInit = 2;
			setTimeout(() => {
				Core.Page._docReadyInit = 3;
				var handler: Function;
				for (var i = 0, l = ctx._docReadyHandlers.length; i < l; i++) {
					handler = ctx._docReadyHandlers[i];
					handler();
				}
			});
		}
		/**
		 * @summary Return environment with name, layout and ext theme info
		 */
		public static GetEnvironment (): Core.Environment {
			var ctx = Core.Page;
			return ctx.GetConfig().Environment;
		}
		public static GetLayout (): Core.Layout {
			var ctx = Core.Page;
			return ctx.GetConfig().Layout;
		}
		public static GetMediaSiteVersion (): Core.MediaSiteVersion {
			var ctx = Core.Page;
			return ctx.GetConfig().MediaSiteVersion;
		}
		/** @summary Get raw string value as JSON data. */
		public static DecodeAttrJson (encodedAttrValue: string | null): any {
			if (encodedAttrValue == null) return null;
			return JSON.parse(encodedAttrValue);
		}
		/** @summary Get base page config. */
		public static GetConfig (): Core.Configs.IConfig {
			var ctx = Core.Page;
			if (ctx.config == null)
				ctx.config = this.createConfig();
			return ctx.config;
		}
		/** @summary Initialize page base features in static context to not occupy instance context. */
		protected constructor () {
			var ctx: typeof Core.Page = new.target;
			this.Static = ctx;
			if (ctx._coreFeaturesInit) return;
			ctx._coreFeaturesInit = true;
			ctx.config = ctx.GetConfig();
			Core.Page.config = ctx.config;
		}
		/** @summary Create core configuration. */
		protected static createConfig (): Core.Configs.IConfig {
			var configData = this.DecodeAttrJson(
				this.getElmAttr(this.SELECTORS.CORE_CONFIG_ELM_ID)
			) as Core.Configs.IRawData;
			return Object.assign({}, configData, <Core.Configs.IConfig>{
				Environment: new Core.Environment(configData.Environment),
				Layout: new Core.Layout(configData.Layout),
				MediaSiteVersion: new Core.MediaSiteVersion(configData.MediaSiteVersion),
				Controller: configData.Controller,
				Action: configData.Action
			});
		}
		/** @summary Get any DOM element attribute value by element id. */
		protected static getElmAttr (elmId: string, attrName: string = 'value'): string | null {
			var elm = document.getElementById(elmId) as HTMLInputElement;
			return elm == null
				? null
				: elm.getAttribute(attrName);
		}
	}
}