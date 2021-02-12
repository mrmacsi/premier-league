<template>
    <div class="card m-2">
        <div class="p-3">
            <table>
                <tr>
                    <td>
                        <table style="width: 400px">
                            <tr>
                                <td colspan="9"><h1>League Table</h1></td>
                            </tr>
                            <tr class="col">
                                <th>#</th>
                                <th>Teams</th>
                                <th>PTS</th>
                                <th>P</th>
                                <th>W</th>
                                <th>D</th>
                                <th>L</th>
                                <th>GD</th>
                            </tr>
                            <tr class="wpos" v-if="stats && stats.length" v-for="(stat, index) in stats">
                                <td> {{ index + 1 }}</td>
                                <td>{{ stat.team_name }}</td>
                                <td>{{ stat.points }}</td>
                                <td>{{ stat.played }}</td>
                                <td>{{ stat.win }}</td>
                                <td>{{ stat.draw }}</td>
                                <td>{{ stat.lose }}</td>
                                <td>{{ stat.goal_difference }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="vertical-align: baseline;">
                        <table style="width: 400px">
                            <tr style="width: 200px">
                                <td colspan="5"><h1>Match Results</h1></td>
                            </tr>
                            <tr style="width: 200px">
                                <td colspan="5"><h3>{{ week }}. Week Match Results</h3></td>
                            </tr>
                            <tr v-if="matches && matches.length" v-for="(match, index) in matches">
                                <td>{{ match.home_team_name }}</td>
                                <td>{{ match.home_team_score }}</td>
                                <td>-</td>
                                <td>{{ match.competitor_team_score }}</td>
                                <td>{{ match.competitor_team_name }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: [
        'matches',
        'week',
        'stats'
    ],
    // Fetches posts when the component is created.
    created() {
    }
}
</script>
