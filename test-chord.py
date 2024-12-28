class Node:
    def __init__(self, id):
        self.id = id
        self.data = {}
        self.successor = None

    def find_successor(self, id):
        if self.successor is None or (self.id < id <= self.successor.id):
            return self.successor
        else:
            return self.successor.find_successor(id)

    def insert_data(self, key, value):
        self.data[key] = value

    def get_data(self, key):
        return self.data.get(key, None)

class Chord:
    def __init__(self):
        self.nodes = []

    def add_node(self, id):
        new_node = Node(id)
        if not self.nodes:
            new_node.successor = new_node
        else:
            new_node.successor = self.nodes[0]
            self.nodes[-1].successor = new_node
        self.nodes.append(new_node)

    def find_node(self, id):
        if not self.nodes:
            return None
        return self.nodes[0].find_successor(id)

# Test case
def test_chord():
    chord = Chord()
    chord.add_node(0)
    chord.add_node(1)
    chord.add_node(2)
    chord.add_node(3)

    # Insert data into the node responsible for ID 2
    node_for_data = chord.find_node(2)
    if node_for_data:
        node_for_data.insert_data(2, "Data for ID 2")
    
    # Find data
    node = chord.find_node(2)
    if node:
        data = node.get_data(2)
        print(f"Found data: {data}")
    else:
        print("Node not found")

# Chạy test case
test_chord()