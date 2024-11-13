namespace Core {
	export class MediaSiteVersion {
		private _mediaSiteVersions: Map<MediaSiteVersions.Name, boolean>;
		public IsMobile (): boolean {
			return this._mediaSiteVersions.get(MediaSiteVersions.Name.Mobile);
		}
		public IsFull (): boolean {
			return this._mediaSiteVersions.get(MediaSiteVersions.Name.Full);
		}
		public constructor (mediaSiteVersion: MediaSiteVersions.Name) {
			var mediaSiteVersionNames = Object.keys(MediaSiteVersions.Name);
			this._mediaSiteVersions = new Map<MediaSiteVersions.Name, boolean>();
			for (var mediaSiteVersionName of mediaSiteVersionNames)
				this._mediaSiteVersions.set(MediaSiteVersions.Name[mediaSiteVersionName], false);
			if (this._mediaSiteVersions.has(mediaSiteVersion)) {
				this._mediaSiteVersions.set(mediaSiteVersion, true);
			} else {
				throw new Error(`Unknown media site version: '${mediaSiteVersion}'.`);
			}
		}
	};
}