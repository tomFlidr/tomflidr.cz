namespace Front.Navigations.Mobiles {
	export class TouchHandlers extends Handlers {
		public static readonly HORIZONTAL_FULL_WIDTH_TOLERANCE = 0.5;
		protected Static: typeof Mobiles.TouchHandlers; 
		protected touchStartPositions: Interfaces.IMousePositions;
		public constructor (main: Navigations.Mobile) {
			super(main);
			this.initEvents();
		}
		protected initEvents (): void {
			super.initEvents();
			window.addEventListener(
				'touchstart', this.handleTouchStart.bind(this)
			);
			window.addEventListener(
				'touchend', this.handleTouchEnd.bind(this)
			);
		}
		protected handleTouchStart (e: TouchEvent): void {
			if (e.changedTouches.length !== 1) return; // multiple fingers
			var touch = e.changedTouches[0];
			this.touchStartPositions = <Interfaces.IMousePositions>{
				PageX: touch.pageX,
				PageY: touch.pageY,
			};
		}
		protected handleTouchEnd (e: TouchEvent): void {
			if (e.changedTouches.length !== 1) return; // touch from outside or multiple fingers
			var touch = e.changedTouches[0],
				touchEndPositions = <Interfaces.IMousePositions>{
					PageX: touch.pageX,
					PageY: touch.pageY,
				},
				startPageX = touchEndPositions.PageX,
				startPageXtolerated = window.innerWidth * this.Static.HORIZONTAL_FULL_WIDTH_TOLERANCE,
				horizontalDiff = this.touchStartPositions.PageX - startPageX,
				verticalDiff = this.touchStartPositions.PageX - startPageX,
				toRight = horizontalDiff < 0;
			if (startPageX > startPageXtolerated) 
				return; // touch start has been too much on right side of display
			if (Math.abs(verticalDiff) > Math.abs(horizontalDiff))
				return; // gesture was more vertical
			if (toRight) {
				if (this.main.GetOpened()) 
					return; // navigation already opening/opened
				this.main.Open();
				e.preventDefault();
			} else {
				if (!this.main.GetOpened()) 
					return; // navigation already closing/closed
				this.main.Close();
				e.preventDefault();
			}
		}
	}
}