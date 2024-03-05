export default [
    {
        id: 0,
        title: 'Today',
        start: new Date(new Date().setHours(new Date().getHours() - 3)),
        end: new Date(new Date().setHours(new Date().getHours() + 3)),
        color: '#bbb',
    },
    {
        id: 1,
        title: 'Test 01',
        start: new Date(new Date().setHours(new Date().getHours() - 24)),
        end: new Date(new Date().setHours(new Date().getHours() - 21)),
        color: '#ddd',
    },
    {
        id: 2,
        title: 'Test 02',
        start: new Date(new Date().setHours(new Date().getHours() - 48)),
        end: new Date(new Date().setHours(new Date().getHours() - 45)),
        color: '#444',
    },
    {
        id: 3,
        title: 'Function init',
        start: new Date(new Date().setHours(12, 30)),
        end: new Date(new Date().setHours(15, 30)),
        color: '#000',
    },
    {
        id: 4,
        title: 'Scalar init',
        start: new Date(2019, 9, 7, 17, 0),
        end: new Date(2019, 9, 7, 18, 0),
        color: '#888',
    },
]