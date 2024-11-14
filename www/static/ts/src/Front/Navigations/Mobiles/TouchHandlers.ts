namespace Front.Navigations.Mobiles {
	export class TouchHandlers extends Handlers {
		public static readonly HORIZONTAL_FULL_WIDTH_TOLERANCE = 0.5;
		public static readonly HORIZONTAL_MIN_TOUCH_WIDTH = 40;
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
				horizontalDiff = (this.touchStartPositions?.PageX ?? 0) - startPageX,
				verticalDiff = (this.touchStartPositions?.PageX ?? 0) - startPageX,
				horizontalDiffAbs = Math.abs(horizontalDiff),
				toRight = horizontalDiff < 0;
			if (this.touchStartPositions == null)
				return; // some weird event handling caused by other page components
			if (startPageX > startPageXtolerated) 
				return; // touch start has been too much on right side of display
			if (horizontalDiffAbs < this.Static.HORIZONTAL_MIN_TOUCH_WIDTH)
				return; // gesture was too short
			if (Math.abs(verticalDiff) > horizontalDiffAbs)
				return; // gesture was more vertical
			this.touchStartPositions = null;
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