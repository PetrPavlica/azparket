import PropTypes from 'prop-types'
import React, { Component } from 'react'
import { navigate } from 'react-big-calendar/lib/utils/constants'

class MyToolbar extends Component {
    render() {
        let {
            localizer: { messages },
            label,
        } = this.props;

        return (
            <div className="rbc-toolbar">
        <span className="rbc-btn-group">
          <button
              type="button"
              onClick={this.navigate.bind(null, navigate.TODAY)}
          >
            {messages.today}
          </button>
          <button
              type="button"
              onClick={this.navigate.bind(null, navigate.PREVIOUS)}
          >
            {messages.previous}
          </button>
          <button
              type="button"
              onClick={this.navigate.bind(null, navigate.NEXT)}
          >
            {messages.next}
          </button>
            <button
                type="button"
                style={{ marginLeft: '30px' }}
                onClick={this.navigate.bind(null, 'REFRESH')}
            >
            {messages.refresh}
          </button>
        </span>

                <span className="rbc-toolbar-label">{label}</span>

                <span className="rbc-btn-group">{this.viewNamesGroup(messages)}</span>
            </div>
        )
    }

    navigate = action => {
        this.props.onNavigate(action)
    };

    view = view => {
        this.props.onView(view)
    };

    viewNamesGroup(messages) {
        let viewNames = this.props.views;
        const view = this.props.view;

        if (viewNames.length > 1) {
            return viewNames.map(name => (
                <button
                    type="button"
                    key={name}
                    className={ view === name ? 'rbc-active' : '' }
                    onClick={this.view.bind(null, name)}
                >
                    {messages[name]}
                </button>
            ))
        }
    }
}

MyToolbar.propTypes = {
    view: PropTypes.string.isRequired,
    views: PropTypes.arrayOf(PropTypes.string).isRequired,
    label: PropTypes.node.isRequired,
    localizer: PropTypes.object,
    onNavigate: PropTypes.func.isRequired,
    onView: PropTypes.func.isRequired,
};

export default MyToolbar