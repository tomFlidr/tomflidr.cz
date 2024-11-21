namespace Front.Navigations.Mobiles {
	export abstract class Handlers {
		protected Static: typeof Mobiles.Handlers; 
		protected main: Mobile;
		protected members: Interfaces.IMembers;
		protected navigationOpened: boolean;
		protected documentClickHandler: (e?: MouseEvent) => void;
		protected containerWidth: number;
		public constructor (main: Navigations.Mobile) {
			this.Static = new.target;
			this.main = main;
			this.members = this.main.GetElements().GetMembers();
			this.documentClickHandler = this.handleDocumentClick.bind(this);
		}
		public HandleOpened (opened: boolean = true): this {
			this.navigationOpened = opened;
			if (opened) {
				document.addEventListener('click', this.documentClickHandler);
				this.initContainerWidth();
				
			} else {
				document.removeEventListener('click', this.documentClickHandler);
			}
			return this;
		}
		protected initEvents (): void {
			this.members.OpenBtn.addEventListener(
				'click', this.handleOpenBtnClick.bind(this)
			);
			this.members.CloseBtn.addEventListener(
				'click', this.handleCloseBtnClick.bind(this)
			);
		}
		protected handleOpenBtnClick (e?: MouseEvent): void {
			if (this.main.GetOpened()) return;
			this.main.Open();
			e.preventDefault();
		}
		protected handleCloseBtnClick (e?: MouseEvent): void {
			if (!this.main.GetOpened()) return;
			this.main.Close();
			e.preventDefault();
		}
		protected handleDocumentClick (e?: MouseEvent): void {
			if (!this.navigationOpened) return;
			if (e.clientX > this.containerWidth) {
				this.main.Close();
			}
		}
		protected initContainerWidth (): this {
			var container = this.main.GetElements().GetMembers().Container;
			this.containerWidth = container.offsetWidth;
			var interval = setInterval(() => {
				if (this.containerWidth === container.offsetWidth) {
					clearInterval(interval);
				} else {
					this.containerWidth = container.offsetWidth;
				}
			}, 100);
			return this;
		}
	}
}