namespace Front.Navigations.Mobiles {
	export abstract class Handlers {
		protected Static: typeof Mobiles.Handlers; 
		protected main: Mobile;
		protected members: Interfaces.IMembers;
		public constructor (main: Navigations.Mobile) {
			this.Static = new.target;
			this.main = main;
			this.members = this.main.GetElements().GetMembers();
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
	}
}