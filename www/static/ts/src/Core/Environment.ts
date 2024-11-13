namespace Core {
	export class Environment {
		protected envs: Map<Environments.Name, boolean>;
		protected layout: Layout;
		protected mediaSiteVersion: Core.MediaSiteVersion;
		protected touchDevice: boolean;
		public IsDev (): boolean {
			return this.envs.get(Environments.Name.Dev);
		}
		public IsAlpha (): boolean {
			return this.envs.get(Environments.Name.Alpha);
		}
		public IsBeta (): boolean {
			return this.envs.get(Environments.Name.Beta);
		}
		public IsProduction (): boolean {
			return this.envs.get(Environments.Name.Production) || this.envs.get(Environments.Name.Gamma);
		}
		public GetTouchDevice (): boolean {
			return this.touchDevice;
		}
		public constructor (envName: Core.Environments.Name) {
			var envNames = Object.keys(Environments.Name);
			this.envs = new Map<Environments.Name, boolean>();
			for (var name of envNames)
				this.envs.set(Environments.Name[name], false);
			if (this.envs.has(envName)) {
				this.envs.set(envName, true);
			} else {
				throw new Error(`Unknown environment: '${envName}'.`);
			}
			this.touchDevice = (
				('ontouchstart' in window) ||
				(navigator['msMaxTouchPoints'] > 0)
			);
		}
	};
}