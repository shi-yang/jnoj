import { createContext } from 'react';

const GroupContext = createContext({
  id: 0,
  name: '',
  description: '',
  userId: 0,
  role: '',
  membership: 0,
  privacy: 0,
  invitationCode: '',
  type: '',
  team: null,
});

export default GroupContext;
