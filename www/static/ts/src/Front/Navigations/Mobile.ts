namespace Front.Navigations {
	export class Mobile {
		protected page: Core.Page;
		protected elements: Mobiles.Elements;
		protected handlers: Mobiles.Handlers;
		protected opened: boolean = false;
		protected externalHandlers: Map<keyof Mobiles.Interfaces.IEventsMap, Mobiles.EventHandler[]>;
		protected cssAnimationTimeout: number | null;
		public constructor (page: Core.Page) {
			this.page = page;
			this.elements = new Mobiles.Elements(this);
			this.handlers = new Mobiles.TouchHandlers(this);
			this.externalHandlers = new Map<keyof Mobiles.Interfaces.IEventsMap, Mobiles.EventHandler[]>();
		}
		public GetElements (): Mobiles.Elements {
			return this.elements;
		}
		public GetOpenCloseHandlers (): Mobiles.Handlers {
			return this.handlers;
		}
		public GetOpened (): boolean {
			return this.opened;
		}
		public Open (): this {
			this.opened = true;
			this.elements.ShowStart();
			clearTimeout(this.cssAnimationTimeout);
			this.cssAnimationTimeout = setTimeout(() => {
				this.elements.ShowEnd();
				this.handlers.HandleOpened(true);
			}, this.elements.GetCssAnimDuration());
			return this;
		}
		public Close (): this {
			this.opened = false;
			this.handlers.HandleOpened(false);
			this.elements.CloseStart();
			clearTimeout(this.cssAnimationTimeout);
			this.cssAnimationTimeout = setTimeout(() => {
				this.elements.CloseEnd();
			}, this.elements.GetCssAnimDuration());
			return this;
		}
		public AddEventListener <TEventName extends keyof Mobiles.Interfaces.IEventsMap>(eventName: TEventName, handler: (e: Mobiles.Interfaces.IEventsMap[TEventName]) => void): this {
			var extEvents = this.externalHandlers.has(eventName)
				? this.externalHandlers.get(eventName)
				: [];
			extEvents.push(handler as Mobiles.EventHandler);
			this.externalHandlers.set(eventName, extEvents);
			return this;
		}
		public RemoveEventListener <TEventName extends keyof Mobiles.Interfaces.IEventsMap>(eventName: TEventName, handler: (e: Mobiles.Interfaces.IEventsMap[TEventName]) => void): this {
			var extEvents = this.externalHandlers.has(eventName)
				? this.externalHandlers.get(eventName)
				: [];
			var newExtEvents = [];
			for (var extEventsItem of extEvents)
				if (extEventsItem !== handler)
				newExtEvents.push(extEventsItem);
			this.externalHandlers.set(eventName, newExtEvents);
			return this;
		}
		public FireEvent <TEventName extends keyof Mobiles.Interfaces.IEventsMap>(eventName: TEventName, event: Mobiles.Interfaces.IEventsMap[TEventName]): boolean {
			var continueNextEvents = true;
			if (!this.externalHandlers.has(eventName)) 
				return continueNextEvents;
			var extEvents = this.externalHandlers.get(eventName);
			event
				.SetEventName(eventName)
				.SetMainNavigation(this);
			for (var handler of extEvents) {
				try {
					handler(event);
				} catch (e) {}
			}
			return continueNextEvents;
		}
		protected isTouchDevice (): boolean {
			return (
				('ontouchstart' in window) ||
				(navigator['msMaxTouchPoints'] > 0)
			);
		}
	}
}