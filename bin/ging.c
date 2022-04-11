#include <iostream>

using namespace std;

class Vector3 {
    private:
        int a, b, c;
    public:
        Vector3(int a1 = 0, int b1 = 0, int c1 = 0) {
            a = a1;
            b = b1;
            c = c1;
        }

        friend istream &operator>>(istream  &input, Vector3 &v) {
            cout << "Nhap x,y,z:" << endl;
            input >> v.a >> v.b >> v.c;
            return input;
        }

        friend ostream &operator<<(ostream &output, const Vector3 &v) {
            output << "(" << v.a << ", " << v.b << ", " << v.c << ")";
            return output;
        }

        Vector3 operator+(const Vector3& other){
            return Vector3(this->a + other.a, this->b + other.b, this->c + other.c);
        }

        Vector3 operator-(const Vector3& other) {
            return Vector3(this->a - other.a, this->b - other.b, this->c - other.c);
        }

        ~Vector3() {
            a = b = c = 0;
        }
};

int main()
{
    Vector3 a, b;
    cin >> a;
    cin >> b;
    cout << a + b << endl;
}
