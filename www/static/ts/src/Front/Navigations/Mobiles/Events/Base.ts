namespace Front.Navigations.Mobiles.Events {
	export class Base implements IBase {
		protected mainNavigation: Navigations.Mobile;
		protected eventName: keyof Mobiles.Interfaces.IEventsMap;
		public SetMainNavigation (mainNavigation: Navigations.Mobile): Base {
			this.mainNavigation = mainNavigation;
			return this;
		}
		public GetMainNavigation (): Navigations.Mobile {
			return this.mainNavigation;
		}
		public SetEventName (eventName: keyof Mobiles.Interfaces.IEventsMap): Base {
			this.eventName = eventName;
			return this;
		}
		public GetEventName (): keyof Mobiles.Interfaces.IEventsMap {
			return this.eventName;
		}
	}
}