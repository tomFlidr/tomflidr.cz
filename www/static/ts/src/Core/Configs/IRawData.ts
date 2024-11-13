declare namespace Core.Configs {
	interface IRawData {
		Environment: Core.Environments.Name;
		Layout: Core.Layouts.Name;
		MediaSiteVersion: Core.MediaSiteVersions.Name;
		Controller: string;
		Action: string;
	}
}