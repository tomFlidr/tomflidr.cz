declare namespace Core.Configs {
	interface IConfig {
		Environment: Core.Environment;
		Layout: Core.Layout;
		MediaSiteVersion: Core.MediaSiteVersion;
		Controller: string;
		Action: string;
	}
}