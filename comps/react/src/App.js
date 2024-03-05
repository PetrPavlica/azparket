import React, { Component } from "react";
import { Calendar as BigCalendar, momentLocalizer } from "react-big-calendar";
import moment from "moment";
import 'moment/locale/cs';
import withDragAndDrop from "react-big-calendar/lib/addons/dragAndDrop";

import Axios from 'axios';

import "./App.css";
import messages from './enum/messages';
import MyToolbar from './MyToolbar';
import "react-big-calendar/lib/addons/dragAndDrop/styles.css";
import "./react-big-calendar.css";

moment.locale('cs');
const localizer = momentLocalizer(moment);
const DnDCalendar = withDragAndDrop(BigCalendar);

/*global BASEURL*/

class App extends Component {
    constructor(props) {
        super(props);
        this.state = {
            events: [],
            test: [],
            type: document.getElementById("calendar-type-help").innerText,
        };

        this.moveEvent = this.moveEvent.bind(this);
        this.newEvent = this.newEvent.bind(this);
        this.newEventVisit = this.newEventVisit.bind(this);
        this.editEvent = this.editEvent.bind(this);
        this.editEventVisit = this.editEventVisit.bind(this);
        this.editEventExternServiceVisit = this.editEventExternServiceVisit.bind(this);
        this.resizeEvent = this.resizeEvent.bind(this);
        this.changeView = this.changeView.bind(this);
        this.changeNavigate = this.changeNavigate.bind(this);
    }

    componentDidMount() {
        this.GetEvents(false);
    }

    componentDidUpdate(prevProps, prevState) {
        console.log(prevState);
    }

    GetEvents(isDay) {
        let _this = this;
        Axios.post(BASEURL + '/api/event/get-events', {type: _this.state.type})
            .then(function (response) {
                let finalEvs = [];
                let evs = response.data.success.events;

                for (let key in evs) {
                    // skip loop if the property is from prototype
                    if (!evs.hasOwnProperty(key)) continue;

                    let obj = evs[key];
                    finalEvs[obj['id']] = {
                        id: obj['id'],
                        title: obj['title'],
                        start: new Date(obj['y'], obj['m'], obj['d'], obj['sh'], obj['sm']),
                        end: new Date(obj['y'], obj['m'], obj['d'], obj['eh'], obj['em']),
                        color: obj['color'],
                        desc: obj['desc']
                    };
                }

                _this.setState({events: finalEvs});
            }).catch(function (error) {
                console.log(error);
        });
    }

    ChangeEvent(event) {
        let _this = this;
        Axios.post(BASEURL + '/api/event/change-event', {
            id: event['id'],
            y: event['start'].getFullYear(),
            m: event['start'].getMonth()+1,
            d: event['start'].getDate(),
            sh: event['start'].getHours(),
            sm: event['start'].getMinutes(),
            eh: event['end'].getHours(),
            em: event['end'].getMinutes(),
            type: _this.state.type
        }).then(function (response) {
            if (response.data.success) {
                console.log('DONE');
                console.log(response.data.success);
            }
        }).catch(function (error) {
            console.log(error);
        });
    }

    changeView(name) {

    }

    changeNavigate(date, view, name) {
        console.log(name, view);
        if(name === 'REFRESH') {
            this.GetEvents(view === 'day');
        }
    }

    moveEvent({ event, start, end }) {
        const { events } = this.state;

        const idx = events.indexOf(event);
        let allDay = false;

        const updatedEvent = { ...event, start, end, allDay };

        const nextEvents = [...events];
        nextEvents.splice(idx, 1, updatedEvent);

        this.ChangeEvent(updatedEvent);

        this.setState({
            events: nextEvents,
        });


        console.log(event);
        console.log(start, end);
    }

    resizeEvent({ event, start, end }) {
        const { events } = this.state;

        const idx = events.indexOf(event);

        /*const nextEvents = events.map(existingEvent => {
            return existingEvent.id === event.id
                ? { ...existingEvent, start, end }
                : existingEvent
        });*/

        const updatedEvent = { ...event, start, end };

        const nextEvents = [...events];
        nextEvents.splice(idx, 1, updatedEvent);

        this.ChangeEvent(updatedEvent);

        this.setState({
            events: nextEvents,
        });


        console.log(event);
        console.log(start, end);
    };

    editEvent(event) {
        let win = window.open(BASEURL + '/intra/worker-tender/edit/' + event['id'], '_blank');
        win.focus();
    }

    editEventVisit(event) {
        let win = window.open(BASEURL + '/intra/visit/edit/' + event['id'], '_blank');
        win.focus();
    }

    editEventExternServiceVisit(event) {
        let win = window.open(BASEURL + '/intra/extern-service-visit/edit/' + event['id'], '_blank');
        win.focus();
    }

    newEvent(event) {
        console.log(event);
        // let idList = this.state.events.map(a => a.id)
        // let newId = Math.max(...idList) + 1
        // let hour = {
        //   id: newId,
        //   title: 'New Event',
        //   allDay: event.slots.length == 1,
        //   start: event.start,
        //   end: event.end,
        // }
        // this.setState({
        //   events: this.state.events.concat([hour]),
        // })
    }

    newEventVisit(event) {
        console.log(event);

        let nDay = event.start.getDay();

        let mm = event.start.getMonth() + 1;
        let dd = event.start.getDate();
        let ymd = event.start.getFullYear()+ '-' + (mm > 9 ? '' : '0') + mm + '-' + (dd > 9 ? '' : '0') + dd;

        let sMin = event.start.getMinutes();
        let startHM = event.start.getHours() + ':' + (sMin > 9 ? '' : '0') + sMin;

        let eMin = event.end.getMinutes();
        let endHM = event.end.getHours() + ':' + (eMin > 9 ? '' : '0') + eMin;

        let win = window.open(BASEURL + '/intra/visit/edit?day=' + nDay + '&start=' + startHM + '&end=' + endHM + '&ymd=' + ymd, '_blank');
        win.focus();
    }

    render() {
        let intType = parseInt(this.state.type);
        return (
            <div className="App">
                <DnDCalendar
                    defaultDate={new Date()}
                    defaultView={(intType === 0) ? 'day' : "work_week"}
                    views={['week', 'work_week', 'day', 'agenda']}
                    selectable
                    step={(intType === 0 || intType === 2) ? 30 : 5}
                    length={30}
                    timeslots={(intType === 0) ? 2 : 12}
                    min={(intType === 0 || intType === 2) ? new Date(new Date().setHours(0, 0)) : new Date(new Date().setHours(6, 0))}
                    max={(intType === 0 || intType === 2) ? new Date(new Date().setHours(23, 0)) : new Date(new Date().setHours(17, 0))}
                    localizer={localizer}
                    events={this.state.events}
                    eventPropGetter={event => ({
                        style: {
                            backgroundColor: event.color
                        }
                    })}
                    dayLayoutAlgorithm="no-overlap"
                    messages={messages}
                    onEventDrop={this.moveEvent}
                    resizable
                    onEventResize={this.resizeEvent}
                    onSelectSlot={(intType === 0) ? this.newEventVisit : this.newEvent}
                    onDoubleClickEvent={(intType === 0) ? this.editEventVisit : ((intType === 2) ? this.editEventExternServiceVisit : this.editEvent)}
                    onView={this.changeView}
                    onNavigate={this.changeNavigate}
                    onDragStart={console.log}
                    style={{ height: "100vh" }}
                    components={{
                        toolbar: MyToolbar,
                    }}
                />
            </div>
        );
    }
}

export default App;