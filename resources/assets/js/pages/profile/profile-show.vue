<style lang="scss">
    @import './profile-show.scss';
</style>

<template>
    <div id="page"
         class="profile-page"
    >
        <main-header>
            <banner :parameters.once="topBannerParameters"
                    id="top-banner"
                    class="banner--slim"
                >
                <div>
                    <h1>{{ state.battle_tag }}</h1>
                    <h3>{{ state.stats && state.stats.clan_name || '' }}</h3>
                    <h6>{{ state.region | region}}</h6>
                </div>
            </banner>
        </main-header>

        <main-content>
            <h2 class="section-header">Profile</h2>
            <section class="profile-section">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="block">
                                <div class="block__body">
                                    <a :href="state | battlenet"
                                       class="battlenet-link"
                                       target="_blank"
                                    >
                                        Battle.net
                                    </a>
                                    <p>
                                        <small>Update Available: {{ state.available_in || 'Now' }}</small>
                                    </p>
                                    <button class="btn btn--secondary btn-lg btn--icon"
                                            @click="updateProfile"
                                            :disabled="! state.queueable"
                                    >
                                        Update <i class="fa fa-refresh"></i>
                                    </button>
                                    <bounce v-if="loadingAnimation"
                                            transition="fade"
                                            class="animated"
                                    ></bounce>
                                </div>
                                <div class="block__body block__body--flush"
                                     v-if="state.season_rankings.length > 0"
                                >
                                    <div class="block__row">
                                        <h5 class="block__header">Greater rift</h5>
                                        <ul class="list">
                                            <a class="list__item list__item--link"
                                               v-for="ranking in rankings"
                                               href="/leaderboards/{{ ranking.id }}/show"
                                            >
                                                <span class="flex-50">{{ ranking.players == 1 ? 'Solo' : ranking.players + ' Players' }}</span>
                                                <span class="flex-50">{{ ranking.rift_level }}</span>
                                                <span class="list__item--link__arrow">
                                                    <i class="fa fa-angle-right"></i>
                                                </span>
                                            </a>
                                        </ul>
                                    </div>
                                </div>
                                <div class="block__body"
                                     v-if="state.stats"
                                     transition="fade"
                                >
                                    <div class="block__row">
                                        <h5 class="block__header">Season</h5>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col divider">
                                                <h3 class="text--tertiary block__col__header">
                                                    {{ state.stats.paragon_level_season | number }}
                                                </h3>
                                                <small>softcore</small>
                                            </div>
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col">
                                                <h3 class="text--secondary block__col__header">
                                                    {{ state.stats.paragon_level_season_hardcore | number }}
                                                </h3>
                                                <small>hardcore</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="block__row">
                                        <h5 class="block__header">Era</h5>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col divider">
                                                <h3 class="text--tertiary block__col__header">
                                                    {{ state.stats.paragon_level | number }}
                                                </h3>
                                                <small>softcore</small>
                                            </div>
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col">
                                                <h3 class="text--secondary block__col__header">
                                                    {{ state.stats.paragon_level_hardcore | number }}
                                                </h3>
                                                <small>hardcore</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="block__row">
                                        <h5 class="block__header">Statistics</h5>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col divider">
                                                <h5 class="text--quaternary block__col__header">
                                                    {{ state.stats.kills_monsters | number }}
                                                </h5>
                                                <small>monsters</small>
                                            </div>
                                            <div class="col-md-6 col-sm-12 col-xs-12 block__col">
                                                <h5 class="text--quaternary block__col__header">
                                                    {{ state.stats.kills_elites | number }}
                                                </h5>
                                                <small>elites</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-12 col-xs-12"
                             v-if="state.heroes.length > 0"
                        >
                            <ul class="list">
                                <list-item-header></list-item-header>
                                <list-item v-for="hero in state.heroes"
                                           :hero="hero"
                                           v-for="hero in state.heroes"
                                           stagger="100"
                                ></list-item>
                            </ul>
                        </div>
                        <div class="col-md-8 col-sm-12 col-xs-12 text-xs-center p-t-3 p-b-3"
                             v-else
                        >
                            <h1>
                                <i class="fa fa-exclamation-triangle"></i>
                                New Profile Record
                            </h1>
                            <h6>To refresh this profile, click update.</h6>
                        </div>
                    </div>
                </div>
            </section>
        </main-content>
    </div>
</template>

<script type="text/babel">
    import profileStub from '../../stubs/profile';
    import listItem from '../../components/list/list-item.vue';
    import listItemHeader from '../../components/list/list-item-header.vue';

    export default {
        data: function () {
            return {
                state: profileStub,
                topBannerParameters: {
                    background: 'url("/img/profile-banner.jpg") no-repeat fixed 50% 0'
                },
                loadingAnimation: false
            }
        },

        props: ['data'],

        components: {
            listItem,
            listItemHeader
        },

        computed: {
            rankings () {
                let players = []
                this.state.season_rankings.forEach(i => {
                    if (!players.find(j => j.players === i.players)) {
                        players.push(i)
                    }
                })

                return players
            }
        },

        filters: {
            battlenet (state) {
                return `https://${state.region}.battle.net/d3/en/profile/${state.battle_tag.replace('#', '-')}/`;
            }
        },

        ready () {
            this.init()
        },

        methods: {
            init () {
                this.state = JSON.parse(this.data)

                if (this.state.stats == null) {
                    this.$root.message('warning', 'New Profile Record')
                }
            },

            updateProfile () {
                this.loadingAnimation = true
                this.state.queueable = false
                this.$root.message('info', 'Profile is currently in queue.')

                this.$http.patch(`/api/profiles/${this.state.id}`).then(response => {
                    this.state = response.data
                    this.loadingAnimation = false
                    this.$root.message('success', 'Profile updated', 4000)
                }).catch(response => {
                    this.loadingAnimation = false
                    this.$root.message('warning', 'Profile record not found.  This could be due to an error processing the request, or the <strong>Account</strong> was banned. Please try again later')
                })
            }
        }
    }
</script>