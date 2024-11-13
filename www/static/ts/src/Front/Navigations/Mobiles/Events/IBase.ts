declare namespace Front.Navigations.Mobiles.Events {
	interface IBase {
		SetMainNavigation (mainNavigation: Navigations.Mobile): IBase;
		GetMainNavigation (): Navigations.Mobile;
		SetEventName (eventName: keyof Mobiles.Interfaces.IEventsMap): IBase;
		GetEventName (): keyof Mobiles.Interfaces.IEventsMap;
	}
}