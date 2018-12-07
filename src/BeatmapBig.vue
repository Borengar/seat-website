<template lang="pug">
.wrapper(:style="background")
	.version {{ beatmap.version }}
	a.title(:href="beatmap.url" target="_blank") {{ beatmap.beatmapset.title }}
	.artist {{ beatmap.beatmapset.artist }}
	.stats-1-box
	img.length-image.stats-1-images(src="https://osu.ppy.sh/images/layout/beatmapset-page/total_length.svg" title="Length")
	.length.stats-1(title="Length") {{ length }}
	img.bpm-image.stats-1-images(src="https://osu.ppy.sh/images/layout/beatmapset-page/bpm.svg" title="BPM")
	.bpm.stats-1(title="BPM") {{ beatmap.beatmapset.bpm }}
	img.circle-image.stats-1-images(src="https://osu.ppy.sh/images/layout/beatmapset-page/count_circles.svg" title="Circle Count")
	.circle.stats-1(title="Circle Count") {{ beatmap.count_circles }}
	img.slider-image.stats-1-images(src="https://osu.ppy.sh/images/layout/beatmapset-page/count_sliders.svg" title="Slider Count")
	.slider.stats-1(title="Slider Count") {{ beatmap.count_sliders }}
	.stats-2-box
	.cs-label.stats-2-labels Circle Size
	v-progress-linear.cs-progress.stats-2-progress(height="5"  :value="beatmap.cs * 10" color="white")
	.cs.stats-2 {{ beatmap.cs }}
	.drain-label.stats-2-labels HP Drain
	v-progress-linear.drain-progress.stats-2-progress(height="5"  :value="beatmap.drain * 10" color="white")
	.drain.stats-2 {{ beatmap.drain }}
	.accuracy-label.stats-2-labels Accuracy
	v-progress-linear.accuracy-progress.stats-2-progress(height="5"  :value="beatmap.accuracy * 10" color="white")
	.accuracy.stats-2 {{ beatmap.accuracy }}
	.ar-label.stats-2-labels Approach Rate
	v-progress-linear.ar-progress.stats-2-progress(height="5"  :value="beatmap.ar * 10" color="white")
	.ar.stats-2 {{ beatmap.ar }}
	.difficulty-rating-label.stats-2-labels Star Difficulty
	v-progress-linear.difficulty-rating-progress.stats-2-progress(height="5"  :value="beatmap.difficulty_rating * 10" color="yellow")
	.difficulty-rating.stats-2 {{ beatmap.difficulty_rating.toPrecision(2) }}
	a(:href="downloadLink")
		img.download(src="./images/download.png")
	a(:href="directLink")
		img.direct(src="./images/direct.png")
	img.mod(:src="modImage(mod)")
</template>

<script>
export default {
	name: 'BeatmapBig',
	props: {
		beatmap: Object,
		mod: String
	},
	computed: {
		background() {
			return { 'background': 'linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),url(' + this.beatmap.beatmapset.covers['cover@2x'] + ') no-repeat center center' }
		},
		length() {
			let minutes = Math.floor(this.beatmap.total_length / 60)
			let seconds = this.beatmap.total_length % 60
			return minutes + ':' + ('00' + seconds).slice(-2)
		},
		downloadLink() {
			return 'https://osu.ppy.sh/beatmapsets/' + this.beatmap.beatmapset.id + '/download?noVideo=1'
		},
		directLink() {
			return 'osu://dl/' + this.beatmap.beatmapset.id
		}
	},
	methods: {
		modImage(mod) {
			return require('./images/mod-' + mod + '.png')
		}
	}
}
</script>

<style lang="stylus" scoped>
.wrapper
	width 1000px
	height 255px
	position relative
	color white
	text-shadow 2px 2px #000000
	flex-shrink 0
.version
	position absolute
	left 30px
	top 20px
	font-size 17px
.title
	position absolute
	left 30px
	top 80px
	font-size 35px !important
	font-style italic
	font-weight bold
	color white
	text-decoration none
.artist
	position absolute
	left 30px
	top 120px
	font-size 20px
	font-style italic
.stats-1-box
	position absolute
	left 680px
	top 45px
	height 35px
	width 275px
	background linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5))
.stats-1-images
	position absolute
	top 55px
	height 15px
	width 15px
.stats-1
	position absolute
	top 55px
	font-size 12px
	color yellow
.length-image
	left 695px
.length
	left 715px
.bpm-image
	left 760px
.bpm
	left 780px
.circle-image
	left 825px
.circle
	left 845px
.slider-image
	left 885px
.slider
	left 905px
.stats-2-box
	position absolute
	left 680px
	top 85px
	height 115px
	width 275px
	background linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5))
.stats-2-labels
	position absolute
	left 695px
	font-size 12px
.stats-2-progress
	position absolute
	left 790px
	width 110px
.stats-2
	position absolute
	left 920px
	font-size 12px
.cs-progress
	top 88px
.cs-label, .cs
	top 95px
.drain-progress
	top 108px
.drain-label, .drain
	top 115px
.accuracy-progress
	top 128px
.accuracy-label, .accuracy
	top 135px
.ar-progress
	top 148px
.ar-label, .ar
	top 155px
.difficulty-rating-progress
	top 168px
.difficulty-rating-label, .difficulty-rating
	top 175px
.download
	position absolute
	left 690px
	top 200px
	cursor pointer
	width 125px
	height 53px
.direct
	position absolute
	left 820px
	top 200px
	cursor pointer
	width 125px
	height 53px
.mod
	position absolute
	top 150px
	width 90px
	height 87px
	left 30px
</style>