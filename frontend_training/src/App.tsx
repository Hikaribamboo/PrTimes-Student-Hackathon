import React, { useState } from 'react';
import { v4 as uuidv4 } from 'uuid';

function App() {
  const [task, setTask] = useState('');
  const [tasks, setTasks] = useState<
    { id: string; text: string; isEdit: boolean }[]
  >([]);

  const addTask = () => {
    const newTask = { id: uuidv4(), text: task, isEdit: false };
    setTasks([...tasks, newTask]);
    setTask('');
  };

  const completeTask = (id: string) => {
    setTasks(tasks.filter((task) => task.id !== id));
  };

  return (
    <div>
      <input
        type="text"
        placeholder="TODOを入力"
        value={task}
        onChange={(e) => setTask(e.target.value)}
      />
      <button onClick={addTask}>追加</button>

      <ul>
        {tasks.map((todo) => (
          <li key={todo.id}>
            {todo.text}
            <button onClick={() => completeTask(todo.id)}>完了</button>
          </li>
        ))}
      </ul>
    </div>
  );
}

export default App;

