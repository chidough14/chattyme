import axios from 'axios'

export default {
    state: {
        userList: [],
        userMessage: []
    },
    getters: {
        userList(state) {
            return state.userList
        },
        userMessage(state) {
            return state.userMessage
        }
    },
    mutations: {
        userList(state, payload) {
            state.userList = payload
        },
        userMessage(state, payload) {
            state.userMessage =  payload
        }
    },
    actions: {
        userList(context) {
            axios.get ('userlist')
            .then(response => {
                context.commit('userList', response.data)
            })
            .catch(err => console.log(err))
        },
        userMessage(context, payload){
            axios.get ('usermessages/'+payload)
            .then(res => {
                context.commit('userMessage', res.data)
            })
            .catch(err => console.log(err))
        }
    }
}
